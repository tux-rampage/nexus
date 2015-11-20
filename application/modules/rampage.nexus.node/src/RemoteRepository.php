<?php
/**
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2015 LUKA netconsult GmbH (www.luka.de)
 */

namespace rampage\nexus\node;

use Zend\Http\Client as HttpClient;
use Zend\Uri\Http as HttpUri;

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
        // TODO Auto-generated method stub

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
    public function findPackageArchive(\rampage\nexus\entities\ApplicationInstance $application)
    {
        // TODO Auto-generated method stub

    }
}
