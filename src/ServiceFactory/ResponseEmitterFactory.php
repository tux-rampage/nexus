<?php
/**
 * @author    Axel Helmert <ah@luka.de>
 * @license   LUKA Proprietary
 * @copyright Copyright (c) 2016 LUKA netconsult GmbH (www.luka.de)
 */

namespace Rampage\Nexus\ServiceFactory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Emitter\EmitterStack;
use Rampage\Nexus\Response\SapiStreamEmitter;
use Zend\Diactoros\Response\SapiEmitter;

class ResponseEmitterFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $stack = new EmitterStack();
        $stack->push(new SapiEmitter());
        $stack->unshift(new SapiStreamEmitter());

        return $stack;
    }
}
