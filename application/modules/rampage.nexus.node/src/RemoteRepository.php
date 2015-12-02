<?php
/**
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2015 LUKA netconsult GmbH (www.luka.de)
 */

namespace rampage\nexus\node;

use rampage\nexus\RequestSignatureInterface;

use rampage\nexus\entities\ApplicationInstance;
use rampage\nexus\exceptions\ApiCallException;
use rampage\nexus\exceptions\LogicException;
use rampage\nexus\node\hydration\ApplicationInstanceHydrator;
use rampage\nexus\node\hydration\HydratingArrayCollection;

use Zend\Http\Client as HttpClient;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Response\Stream as HttpStreamResponse;
use Zend\Http\Client\Adapter\Curl;

use Zend\Uri\Http as HttpUri;

use Zend\Json\Json;


/**
 * Remote repository
 */
class RemoteRepository implements RepositoryInterface
{
    /**
     * @var HttpUri
     */
    protected $uri;

    /**
     * @var string
     */
    protected $localPackageDir = null;

    /**
     * @var RequestSignatureInterface
     */
    protected $signature;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var ApplicationInstanceHydrator
     */
    protected $hydrator;

    /**
     * @param string $uri The Remote server URI
     * @param DbAdapter $db
     */
    public function __construct(RequestSignatureInterface $signature, $uri, $localPackageDir)
    {
        $this->uri = ($uri instanceof HttpUri)? $uri : new HttpUri($uri);
        $this->signature = $signature;
        $this->localPackageDir = $localPackageDir;

        $this->httpClient = new HttpClient();
        $this->hydrator = new ApplicationInstanceHydrator();

        $this->httpClient->setAdapter(new Curl());
    }

    /**
     * Perform a get request
     *
     * @param string $path
     * @return \Zend\Http\Response
     */
    protected function get($path)
    {
        $uri = clone $this->uri;
        $uri->setPath($uri->getPath() . $path);

        $this->httpClient->setUri($uri);
        $this->signature->sign($this->httpClient->getRequest());

        return $this->httpClient->send();
    }

    /**
     * Perform a HEAD request
     *
     * @param string $path
     * @return \Zend\Http\Response
     */
    protected function head($path)
    {
        $uri = clone $this->uri;
        $uri->setPath($uri->getPath() . $path);

        $this->httpClient->setUri($uri);
        $this->httpClient
            ->getRequest()
            ->setMethod(HttpRequest::METHOD_HEAD);

        $this->signature->sign($this->httpClient->getRequest());

        return $this->httpClient->send();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllApplications()
    {
        $this->httpClient->reset();

        $response = $this->get('/applications');
        if (!$response->isSuccess()) {
            throw new ApiCallException('Failed to get application list');
        }

        $data = Json::decode($response->getBody(), Json::TYPE_ARRAY);
        $list = [];

        if (isset($data['aplications'])) {
            $list = $data['applications'];
        }

        $collection = new HydratingArrayCollection($list, new ApplicationInstanceHydrator(), new ApplicationInstance());
        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function findApplication($id)
    {
        $this->httpClient->reset();

        $response = $this->get('/applications/' . $id);

        if ($response->isNotFound()) {
            return null;
        }

        if (!$response->isSuccess()) {
            throw new ApiCallException('Failed to get application ' . $id);
        }

        $data = Json::decode($response->getBody(), Json::TYPE_ARRAY);
        return $this->hydrator->hydrate($data);
    }

    /**
     * @todo Unused package removal
     * @see \rampage\nexus\node\RepositoryInterface::findPackageArchive()
     */
    public function findPackageArchive(ApplicationInstance $application)
    {
        $package = $application->getPackage();

        if (!$package) {
            throw new LogicException('Application instance does not define a package');
        }

        $toFile = sprintf('%s/%s.pkg', $this->localPackageDir, $package->getId());
        $path = sprintf('package/%s', $package->getId());

        // There already is a file, check if download should be started
        if (file_exists($toFile)) {
            $md5 = md5_file($toFile);
            $this->httpClient->reset();
            $response = $this->head($path);

            if (!$response->isSuccess()) {
                throw new ApiCallException(sprintf(
                    'Failed to find application package file for "%s" (%s)',
                    $application->getName(),
                    $application->getId()
                ));
            }

            $header = $response->getHeaders()->get('X-Package-Md5');

            if (!$header) {
                throw new ApiCallException(sprintf(
                    'Invalid package file info response for application "%s" (%s)',
                    $application->getName(),
                    $application->getId()
                ));
            }

            if ($md5 == $header->getFieldValue()) {
                return new \SplFileInfo($toFile);
            }
        }

        $this->httpClient->reset();
        $this->httpClient->setStream($toFile);

        $response = $this->get($path);

        if (!$response->isSuccess() || !($response instanceof HttpStreamResponse)) {
            throw new ApiCallException(sprintf(
                'Failed to download application package file for "%s" (%s)',
                $application->getName(),
                $application->getId()
            ));
        }

        return new \SplFileInfo($toFile);
    }
}
