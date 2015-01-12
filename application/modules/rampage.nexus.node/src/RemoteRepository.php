<?php
/**
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2015 LUKA netconsult GmbH (www.luka.de)
 */

namespace rampage\nexus\node;

use Zend\Db\Adapter\AdapterInterface as DbAdapterInterface;
use Zend\Db\Sql\Sql;

use Zend\Http\Client as HttpClient;
use Zend\Uri\Http as HttpUri;

/**
 * Remote repository
 */
class RemoteRepository implements RepositoryInterface
{
    /**
     * @var Sql
     */
    protected $sql;

    /**
     * @var DbAdapterInterface
     */
    protected $db;

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
    public function __construct($uri, DbAdapterInterface $db)
    {
        $this->db = $db;
        $this->sql = new Sql($db);
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
     * {@inheritdoc}
     */
    public function findDeployedApplications()
    {
        // TODO Auto-generated method stub

    }


}
