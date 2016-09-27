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

namespace Rampage\Nexus\OAuth2\ServiceFactory;

use Interop\Container\ContainerInterface;

use League\OAuth2\Server\AuthorizationServer;

use Zend\Crypt\PublicKey\Rsa\PrivateKey;
use Zend\Di\DependencyInjectionInterface;
use Zend\ServiceManager\Factory\FactoryInterface;


/**
 * Factory for the authorization service
 */
class AuthorizationServerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var $di DependencyInjectionInterface */
        /* @var $pk PrivateKey */
        $di = $container->get(DependencyInjectionInterface::class);
        $pk = $container->get(PrivateKey::class);

        return $di->newInstance(AuthorizationServer::class, [
            'privateKey' => $pk->toString(),
            'publicKey' => $pk->getPublicKey()->toString()
        ]);
    }
}
