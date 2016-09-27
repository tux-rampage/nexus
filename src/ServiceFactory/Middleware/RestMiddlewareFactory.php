<?php
/**
 * @author    Axel Helmert <ah@luka.de>
 * @license   LUKA Proprietary
 * @copyright Copyright (c) 2016 LUKA netconsult GmbH (www.luka.de)
 */

namespace Rampage\Nexus\ServiceFactory\Middleware;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Expressive\Application;
use Zend\Expressive\Router\FastRouteRouter;


class RestMiddlewareFactory implements FactoryInterface
{
    const REST_MIDDLEWARE = 'Rampage\Nexus\Middleware\RestApi';
    const FINAL_HANDLER = 'Zend\Expressive\FinalHandler';

    use ApplicationFactoryTrait;

    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $router = new FastRouteRouter();
        $middleware = new Application($router, $container);
        $config = $container->get('config');
        $config = (isset($config['rest']))? $config['rest'] : [];

        $this->injectRoutesAndPipeline($middleware, $config);
        return $middleware;
    }
}
