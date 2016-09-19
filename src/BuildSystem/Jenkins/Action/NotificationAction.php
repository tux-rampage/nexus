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

namespace Rampage\Nexus\BuildSystem\Jenkins\Action;

use Rampage\Nexus\BuildSystem\Jenkins\Repository\InstanceRepositoryInterface;
use Rampage\Nexus\BuildSystem\Jenkins\PackageScanner\PackageScannerInterface;
use Rampage\Nexus\BuildSystem\Jenkins\BuildNotification;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Zend\Stratigility\MiddlewareInterface;


/**
 * Build notification action middleware
 */
class NotificationAction implements MiddlewareInterface
{
    /**
     * @var InstanceRepositoryInterface
     */
    private $repository;

    /**
     * @var PackageScannerInterface
     */
    private $scanner;

    /**
     * @param PackageScannerInterface $scanner
     * @param InstanceRepositoryInterface $repository
     */
    public function __construct(PackageScannerInterface $scanner, InstanceRepositoryInterface $repository)
    {
        $this->scanner = $scanner;
        $this->repository = $repository;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stratigility\MiddlewareInterface::__invoke()
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $out = null)
    {
        $id = $request->getAttribute('jenkinsInstanceId');
        $notification = new BuildNotification($request->getParsedBody());

        if ($id) {
            $instance = $this->repository->find($id);
            $this->scanner->notify($instance, $notification);
        } else {
            $instances = $this->repository->findByBuildNotification($notification);
            foreach ($instances as $instance) {
                $this->scanner->notify($instance, $notification);
            }
        }
    }
}
