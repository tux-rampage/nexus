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

use Rampage\Nexus\Repository\NodeRepositoryInterface;
use Rampage\Nexus\Archive\ArchiveLoaderInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Zend\Stratigility\MiddlewareInterface;
use Zend\Diactoros\Stream;


/**
 * Package middleware
 */
class PackageAction implements MiddlewareInterface
{
    /**
     * @var NodeRepositoryInterface
     */
    protected $repository;

    /**
     * @var ArchiveLoaderInterface
     */
    private $archiveLoader;

    /**
     * @param NodeRepositoryInterface $repository
     */
    public function __construct(NodeRepositoryInterface $repository, ArchiveLoaderInterface $archiveLoader)
    {
        $this->repository = $repository;
        $this->archiveLoader = $archiveLoader;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stratigility\MiddlewareInterface::__invoke()
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $node = $request->getAttribute('node');
        $applicationId = $request->getAttribute('applicationId');

        if (!$applicationId || !$node || !$node->isAttached()) {
            return $next($request, $response);
        }

        $application = $node->getDeployTarget()->findApplication($applicationId);
        $package = $application? $application->getPackage() : null;
        $archive = $package->getArchive();

        if (!$archive) {
            return $next($request, $response);
        }

        $file = $this->archiveLoader->ensureLocalArchiveFile($archive);
        return $response->withBody(new Stream($file->getPathname()));
    }
}
