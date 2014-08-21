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

namespace rampage\nexus\cluster;

use Exception;

use rampage\nexus\api\ServerApiManager;
use rampage\nexus\entities\ApplicationInstance;
use rampage\nexus\orm\DeploymentRepository;


class ClusterDeployTarget implements DeployTargetInterface
{
    /**
     * @var ServerApiManager
     */
    protected $apiManager;

    /**
     * @var DeploymentRepository
     */
    protected $repository;

    /**
     * @param ServerApiManager $apiManager
     */
    public function __construct(ServerApiManager $apiManager, DeploymentRepository $repository)
    {
        $this->apiManager = $apiManager;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\cluster\DeployTargetInterface::deploy()
     */
    public function deploy(ApplicationInstance $application)
    {
        $cluster = $application->getCluster();

        $application->setState(ApplicationInstance::STATE_STAGING);
        $this->repository->flush($application);

        foreach ($cluster->getServers() as $server) {
            try {
                $api = $this->apiManager->get($server->getType());
                if (!$api->isClusterSupported($cluster)) {
                    throw new \DomainException(sprintf('Unsupported cluster type "%s" for server "%s"', $cluster->getName(), $server->getName()));
                }

                $api->stage($server, $application);
            } catch (Exception $e) {
                $server->setApplicationState($application, ApplicationInstance::STATE_ERROR);
            }
        }
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\cluster\DeployTargetInterface::refreshState()
     */
    public function refreshState(ApplicationInstance $application)
    {
        $cluster = $application->getCluster();

        foreach ($cluster->getServers() as $server) {
            try {
                $api = $this->apiManager->get($server->getType());
                if (!$api->isClusterSupported($cluster)) {
                    throw new \DomainException(sprintf('Unsupported cluster type "%s" for server "%s"', $cluster->getName(), $server->getName()));
                }

                $state = $api->status($server, $application);
            } catch (Exception $e) {
                $state = ApplicationInstance::STATE_ERROR;
            }

            $server->setApplicationState($application, $state);
        }

        $application->updateStateFromServers();
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\cluster\DeployTargetInterface::remove()
     */
    public function remove(ApplicationInstance $application)
    {
        $cluster = $application->getCluster();

        $application->setState(ApplicationInstance::STATE_STAGING);
        $this->repository->flush($application);

        foreach ($cluster->getServers() as $server) {
            try {
                $api = $this->apiManager->get($server->getType());
                if (!$api->isClusterSupported($cluster)) {
                    throw new \DomainException(sprintf('Unsupported cluster type "%s" for server "%s"', $cluster->getName(), $server->getName()));
                }

                $api->deactivate($server, $application);
            } catch (Exception $e) {
                $server->setApplicationState($application, ApplicationInstance::STATE_ERROR);
            }
        }
    }
}
