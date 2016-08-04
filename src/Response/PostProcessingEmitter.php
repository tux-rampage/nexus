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

namespace Rampage\Nexus\Response;

use Zend\Diactoros\Response\EmitterInterface;
use Zend\Stdlib\SplPriorityQueue;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Implements an emitter that will trigger actions after the response was emitted
 */
final class PostProcessingEmitter implements EmitterInterface
{
    /**
     * @var SplPriorityQueue
     */
    private $postProcessors;

    /**
     * @var EmitterInterface
     */
    private $emitter;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param EmitterInterface $emitter
     * @param ContainerInterface $container
     */
    public function __construct(EmitterInterface $emitter, ContainerInterface $container)
    {
        $this->emitter = $emitter;
        $this->container = $container;
        $this->postProcessors = new SplPriorityQueue();
    }

    /**
     * Finish the request
     */
    private function finishRequest()
    {
        // FPM has a nice feature allowing to complete the request for the webserver
        // and browser by closing the connection, so the user does not have to wait for
        // the (possibly time consuming) operations after this call
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        // Make sure the session is closed and all locks are released allowing
        // subsequent requests (Without this call they would block).
        session_write_close();
    }

    /**
     * Runs all post processing actions
     */
    private function executePostProcessingActions()
    {
        foreach ($this->postProcessors as $processor) {
            if (is_string($processor) && $this->container->has($processor)) {
                $processor = $this->container->get('processor');
            }

            if (is_callable($processor)) {
                $processor();
            }
        }
    }

    /**
     * Attach a post processing action
     *
     * @param   string|callable $action     A callable or the name of a callable service. The callable must not expect any parameters
     * @param   number          $priority   The priority for this action
     * @return  self
     */
    public function addPostProcessingAction($action, $priority = 1)
    {
        $this->postProcessors->insert($action, $priority);
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Diactoros\Response\EmitterInterface::emit()
     */
    public function emit(ResponseInterface $response)
    {
        $result = $this->emitter->emit($response);

        if ($this->postProcessors->count() > 0) {
            $this->finishRequest();
            $this->executePostProcessingActions();
        }

        return $result;
    }
}
