<?php
/**
 * Copyright (c) 2016 Axel Helmert
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
 * @copyright Copyright (c) 2016 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\Node\Middleware;

use Rampage\Nexus\Node\DeployStrategyInterface;
use Rampage\Nexus\Node\Repository\ApplicationRepositoryInterface;
use Rampage\Nexus\Node\Repository\VHostRepositoryInterface;

use Rampage\Nexus\Deployment\NodeInterface;
use Rampage\Nexus\Entities\ApplicationInstance;
use Rampage\Nexus\Middleware\DecodeRequestBodyTrait;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

use Zend\Diactoros\Response\JsonResponse;
use Rampage\Nexus\Node\VHostDeployStrategyInterface;


/**
 * Notification middleware
 */
class NotifyMiddleware
{
    use DecodeRequestBodyTrait;

    /**
     * @var ApplicationRepositoryInterface
     */
    private $applicationRepository;

    /**
     * @var VHostRepositoryInterface
     */
    private $vhostRepository;

    /**
     * @var DeployStrategyInterface
     */
    private $deployStrategy;

    /**
     * @param unknown $applicationState
     * @param unknown $nodeState
     * @return string|unknown
     */
    protected function getNodeState($applicationState, $nodeState)
    {
        if (($nodeState == NodeInterface::STATE_FAILURE) || ($applicationState == ApplicationInstance::STATE_ERROR)) {
            return NodeInterface::STATE_FAILURE;
        }

        if ($applicationState != ApplicationInstance::STATE_DEPLOYED) {
            return NodeInterface::STATE_BUILDING;
        }

        return $nodeState;
    }

    /**
     *
     * @param   string  $state  The current node state
     * @return  string          The new node state
     */
    protected function synchronizeVHosts($state)
    {
        if (!$this->deployStrategy instanceof VHostDeployStrategyInterface) {
            return $state;
        }

        foreach ($this->vhostRepository->findAll() as $vhost) {
            if (!$vhost->isOutOfSync()) {
                continue;
            }

            $state = NodeInterface::STATE_BUILDING;
            // FIXME: Add to queue
        }
    }

    /**
     * @param   string  $state  The current node state
     * @return  string          The new node state
     */
    protected function synchronizeApplications($state)
    {
        foreach ($this->applicationRepository->findAll() as $application) {
            if ($application->isOutOfSync()) {
                // TODO: Schedule
                $application->setState(ApplicationInstance::STATE_PENDING);
            }

            $state = $this->getNodeState($application->getState(), $state);
        }

        return $state;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        try {
            $state = $this->synchronizeVHosts(NodeInterface::STATE_READY);
            $state = $this->synchronizeApplications($state);
        } catch (Throwable $e) {
            $state = NodeInterface::STATE_FAILURE;
        }

        return new JsonResponse(['state' => $state]);
    }
}
