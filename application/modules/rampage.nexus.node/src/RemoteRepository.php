<?php
/**
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2015 LUKA netconsult GmbH (www.luka.de)
 */

namespace rampage\nexus\node;

use Zend\Http\Client as HttpClient;
use Zend\Uri\Http as HttpUri;
use rampage\nexus\entities\ApplicationInstance;

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
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @param string $uri The Remote server URI
     * @param DbAdapter $db
     */
    public function __construct($uri)
    {
        $this->uri = ($uri instanceof HttpUri)? $uri : new HttpUri($uri);
        $this->httpClient = new HttpClient();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllApplications()
    {
        $uri = clone $this->uri;
        $uri->setPath($uri->getPath() . '/applications');

        $this->httpClient->setUri($uri);
        // TODO: Implement find all

    }

    /**
     * {@inheritdoc}
     */
    public function findApplication($id)
    {
        // TODO Auto-generated method stub

    }

    /**
     * @see \rampage\nexus\node\RepositoryInterface::findPackageArchive()
     */
    public function findPackageArchive(ApplicationInstance $application)
    {
        // TODO Auto-generated method stub

    }
}
