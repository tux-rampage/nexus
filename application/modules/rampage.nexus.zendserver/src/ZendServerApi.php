<?php
/**
 * Copyright (c) 2014 Axel Helmert
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Axel Helmert
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus\zs;

use rampage\nexus\api\ServerApiInterface;

use rampage\nexus\entities\Server;
use rampage\nexus\entities\Application;

use Zend\Stdlib\Parameters;
use Zend\Uri\Http as HttpUri;


class ZendServerApi implements ServerApiInterface
{
    /**
     * @var ZendServerClient
     */
    protected $client = null;

    /**
     * @var Parameters
     */
    protected $statusMap = array(
        'uploadError' => Application::STATE_ERROR,
        'staging' => Application::STATE_STAGING,
        'stageError' => Application::STATE_ERROR,
        'activating' => Application::STATE_ACTIVATING,
        'deployed' => Application::STATE_DEPLOYED,
        'activateError' => Application::STATE_ERROR,
        'deactivating' => Application::STATE_DEACTIVATING,
        'deactivateError' => Application::STATE_ERROR,
        'unstaging' => Application::STATE_REMOVING,
        'unstageError' => Application::STATE_ERROR,
        'rollingBack' => Application::STATE_STAGING,
        'rollbackError' => Application::STATE_ERROR,
        'partially deployed' => Application::STATE_STAGING
    );

    /**
     * @param ZendServerClient $client
     */
    public function __construct(ZendServerClient $client = null)
    {
        $this->client = $client? : new ZendServerClient();
        $this->statusMap = new Parameters($this->statusMap);
    }

    /**
     * @see \rampage\nexus\api\ServerApiInterface::isClusterSupported()
     */
    public function isClusterSupported(Server $server)
    {
        // Clustering will be supported via ZS only
        return false;
    }

    /**
     * @return ZendServerClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return self
     */
    protected function removeAllApplications()
    {
        $list = $this->client->applicationGetStatus();
        foreach ($list->xpath('./responseData/applicationList/applicationInfo') as $info) {
            if ($info->isDefinedApplication) {
                continue;
            }

            $this->client->applicationRemove((string)$info->id);
        }

        return $this;
    }

    /**
     * @see \rampage\nexus\api\ServerApiInterface::attach()
     */
    public function attach(Server $server)
    {
        $this->removeAllApplications();
        return $this;
    }

    public function deployNew(Server $server, Application $instance)
    {
        $this->client->setUri($server->getUrl());
        $this->client->deployApplication();
    }

    /**
     * @see \rampage\nexus\api\ServerApiInterface::deploy()
     */
    public function deploy(Server $server, Application $instance)
    {
        $this->client->setUri($server->getUrl());
        $appId = $this->client->findDeployedApplicationId($instance->getName());

        if (!$appId) {
            $this->deployNew($server, $instance);
        } else {
            $this->update($server, $appId, $instance);
        }
    }

    /**
     * @see \rampage\nexus\api\ServerApiInterface::detach()
     */
    public function detach(Server $server)
    {
        $this->removeAllApplications();
        return $this;
    }

    /**
     * @see \rampage\nexus\api\ServerApiInterface::getServerName()
     */
    public function getServerName(Server $server)
    {
        $uri = new HttpUri($server->getUrl());
        return $uri->getHost();
    }

    /**
     * @see \rampage\nexus\api\ServerApiInterface::remove()
     */
    public function remove(Server $server, Application $application)
    {
        $this->client->setUri($server->getUrl());

        $id = $this->client->findDeployedApplicationId($application->getName());
        if (!$id) {
            return $this;
        }

        $this->client->applicationRemove($id);
        return $this;
    }

    /**
     * @see \rampage\nexus\api\ServerApiInterface::status()
     */
    public function status(Server $server, Application $application)
    {
        $this->client->setUri($server->getUrl());
        $info = $this->client->applicationGetStatusByName($application->getName());
        $status = (string)$info->status;

        return array(
            'state' => $this->statusMap[$status]? : Application::STATE_UNKNOWN,
            'version' => (string)$info->deployedVersions->deployedVersion
        );
    }
}
