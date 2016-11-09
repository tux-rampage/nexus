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

namespace Rampage\Nexus\ODM\ServiceFactory;

use Doctrine\ODM\MongoDB\Configuration;
use Rampage\Nexus\Config\PropertyConfigInterface;

use Interop\Container\ContainerInterface;

use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Stdlib\Parameters;

/**
 * Config service factory
 */
class ConfigFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var PropertyConfigInterface $runtimeConfig */
        $sysconfig = $container->get('config');
        $runtimeConfig = $container->get('RuntimeConfig');
        $config = new Configuration();

        $params = new Parameters(isset($sysconfig['odm'])? $sysconfig['odm'] : []);
        $autoGenerate = (bool)$params->get('autoGenerateCode', true);
        $codeGenDir = $params->get('codeGeneratorDir', __DIR__ . '/../../../_codegen/odm');

        $config->setDefaultDB($runtimeConfig->get('mongodb.database', 'deployment'));
        $config->setAutoGenerateHydratorClasses($autoGenerate);
        $config->setAutoGeneratePersistentCollectionClasses($autoGenerate);
        $config->setAutoGenerateProxyClasses($autoGenerate);

        $config->setHydratorDir($codeGenDir . '/hydrators');
        $config->setProxyDir($codeGenDir . '/proxies');
        $config->setPersistentCollectionDir($codeGenDir . '/collections');

        $config->setHydratorNamespace('Rampage\Nexus\ODM\Generated\Hydrators');
        $config->setProxyNamespace('Rampage\Nexus\ODM\Generated\Proxies');
        $config->setPersistentCollectionNamespace('Rampage\Nexus\ODM\Generated\PersistentCollections');

        return $config;
    }
}
