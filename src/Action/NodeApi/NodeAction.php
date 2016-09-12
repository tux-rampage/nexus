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

namespace Rampage\Nexus\Action\NodeApi;

use Rampage\Nexus\Entities\AbstractNode;
use Rampage\Nexus\Repository\NodeRepositoryInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Stratigility\MiddlewareInterface;
use Zend\Stdlib\Parameters;

/**
 * Implements the primary entry point for the node
 *
 * This middleware will perform node updates on put requests and
 * Return the deploy target information for get requests
 */
class NodeAction implements MiddlewareInterface
{
    /**
     * @var NodeRepositoryInterface
     */
    protected $repository;

    /**
     * @param NodeRepositoryInterface $repository
     */
    public function __construct(NodeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param array $data
     */
    protected function updateNodeState(AbstractNode $node, array $data)
    {
        $params = new Parameters($data);
        $node->updateState($params->get('state', $node->getState()), $params->get('applicationsState'));
        $this->repository->save($node);
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stratigility\MiddlewareInterface::__invoke()
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $method = strtolower($request->getMethod());
        $node = $request->getAttribute('node');

        if (!$node) {
            return $next($request, $response);
        }

        switch ($method) {
            case 'get':
                return new JsonResponse($node->toArray());

            case 'put':
                $this->updateNodeState($node, $this->decodeJsonRequestBody($request));
                return new EmptyResponse();
        }

        return $next($request, $response);
    }
}
