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

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Stratigility\MiddlewareInterface;
use Rampage\Nexus\Repository\NodeRepositoryInterface;
use Rampage\Nexus\Exception\Http\BadRequestException;
use Rampage\Nexus\Api\RequestSignatureInterface;

/**
 * Prepares the node context after routing
 */
class NodeContextMiddleware implements MiddlewareInterface
{
    /**
     * @var NodeRepositoryInterface
     */
    private $nodeRepository;

    /**
     * @var RequestSignatureInterface
     */
    private $signatureStrategy;

    /**
     * @param NodeRepositoryInterface $nodeRepository
     */
    public function __construct(NodeRepositoryInterface $nodeRepository, RequestSignatureInterface $signatureStrategy)
    {
        $this->nodeRepository = $nodeRepository;
        $this->signatureStrategy = $signatureStrategy;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stratigility\MiddlewareInterface::__invoke()
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $out = null)
    {
        $nodeId = $request->getHeaderLine('X-Node-Id');

        if (!$nodeId) {
            throw new BadRequestException('Missing X-Node-Id header');
        }

        $node = $this->nodeRepository->findOne($nodeId);
        if (!$node) {
            throw new BadRequestException('Invalid node id');
        }

        $this->signatureStrategy->setKey($node->getPublicKey());
        if (!$this->signatureStrategy->verify($request)) {
            throw new BadRequestException('Invalid request signature', BadRequestException::UNAUTHORIZED);
        }

        $request = $request->withAttribute('node', $node);
        return $out($request, $response);
    }
}
