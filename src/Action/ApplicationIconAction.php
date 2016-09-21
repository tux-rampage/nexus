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

namespace Rampage\Nexus\Action;

use Rampage\Nexus\Repository\ApplicationRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Stratigility\MiddlewareInterface;
use Zend\Diactoros\Stream;

/**
 * App Icon action
 */
class ApplicationIconAction implements MiddlewareInterface
{
    /**
     * @var ApplicationRepositoryInterface
     */
    private $repository;

    /**
     * @param ApplicationRepositoryInterface $repository
     */
    public function __construct(ApplicationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stratigility\MiddlewareInterface::__invoke()
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $out = null)
    {
        /* @var $application \Rampage\Nexus\Entities\Application */
        $id = $request->getAttribute('id');
        $application = $this->repository->findOne($id);

        if (!$application) {
            return $out($request, $response);
        }

        $icon = $application->getIcon();

        if (!$icon) {
            $icon = new Stream(__DIR__ . '/../../resources/images/default-app-icon.png');
        }

        return $response->withStatus(200)
            ->withHeader('Content-Type', 'image/png')
            ->withBody($icon);
    }
}
