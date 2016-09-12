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

namespace Rampage\Nexus\ServiceFactory;

use Rampage\Nexus\Exception\RuntimeException;
use Rampage\Nexus\Exception\LogicException;

use Interop\Container\ContainerInterface;

use Zend\Crypt\PublicKey\Rsa\PrivateKey;
use Zend\ServiceManager\Factory\FactoryInterface;


/**
 * Creates a private key instance
 */
class PrivateKeyFactory implements FactoryInterface
{
    use RuntimeConfigTrait;

    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $this->getRuntimeConfig($container);
        $key = $config->get('rsa.keys.private');
        $passPhrase = null;

        if (!$key) {
            throw new RuntimeException('Missing private rsa key in configuration. Please define rsa.keys.private in your runtime configuration.');
        }

        if (is_array($key) || ($key instanceof \ArrayAccess)) {
            if (!isset($key['key'])) {
                throw new LogicException('The key option must be present when rsa.keys.private is defined as array.');
            }

            $passPhrase = (isset($key['passphrase']))? $key['passphrase'] : null;
            $key = $key['key'];
        }

        return new PrivateKey($key, $passPhrase);
    }
}
