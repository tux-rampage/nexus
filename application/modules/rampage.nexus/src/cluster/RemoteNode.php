<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2014 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 */

namespace rampage\nexus\cluster;

use rampage\nexus\api\ServerApiInterface;

use rampage\nexus\entities\ApplicationInstance;
use rampage\nexus\entities\Server;


class RemoteNode implements NodeInterface
{
    /**
     * @var ServerApiInterface
     */
    protected $api = null;

    /**
     * @var Server
     */
    protected $server = null;

    /**
     * @param ServerApiInterface $api
     * @param Server $server
     */
    public function __construct(ServerApiInterface $api, Server $server)
    {
        $this->api = $api;
        $this->server = $server;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\cluster\NodeInterface::activate()
     */
    public function activate(ApplicationInstance $application)
    {
        $this->api->activate($this->server, $application);
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\cluster\NodeInterface::deactivate()
     */
    public function deactivate(ApplicationInstance $application)
    {
        $this->api->deactivate($this->server, $application);
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\cluster\NodeInterface::getId()
     */
    public function getId()
    {
        return $this->server->getId();
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\cluster\NodeInterface::stage()
     */
    public function stage(ApplicationInstance $application)
    {
        $this->api->stage($this->server, $application);
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\cluster\NodeInterface::remove()
     */
    public function remove(ApplicationInstance $application)
    {
        $this->api->remove($this->server, $application);
    }
}
