<?php
/**
 * This is part of rampage-nexus
 * Copyright (c) 2013 Axel Helmert
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
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus\package;

use rampage\nexus\entities\ApplicationInstance;
use rampage\nexus\entities\ApplicationPackage;
use rampage\nexus\entities\PackageParameter;

use rampage\core\GracefulArrayAccess;

use Zend\Json\Json;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodHydrator;

use SplFileInfo;
use PharData;
use RuntimeException;


class ComposerInstaller extends AbstractInstaller
{
    const TYPE_NAME = 'composer';

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\package\ApplicationPackageInterface::getTypeName()
     */
    public function getTypeName()
    {
        return static::TYPE_NAME;
    }

    /**
     * @param array $parameters
     * @return PackageParameter[]
     */
    protected function hydrateParameters(array $parameters)
    {
        $result = [];

        foreach ($parameters as $name => $param) {
            if (!is_string($name) || ($name == '')) {
                continue;
            }

            $parameter = new PackageParameter();
            $parameter->setName($name);

            if (is_string($param)) {
                $parameter->setType($param);
            }

            if (is_array($param)) {
                unset($param['name']);
                (new ClassMethodHydrator())->hydrate($param, $parameter);
            }

            $result[] = $parameter;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function createEntityFromPackageFile(SplFileInfo $archive)
    {
        $phar = new PharData($archive->getPathname());

        if (!isset($phar['composer.json'])) {
            throw new RuntimeException(sprintf('Could not find composer.json in package file "%s"', $archive->getFilename()));
        }

        $json = Json::decode($phar['composer.json']->getContents(), Json::TYPE_ARRAY);
        $wrapper = new GracefulArrayAccess($json? : array());

        if (!$json || !$wrapper->get('name') || !$wrapper->get('version')) {
            throw new RuntimeException(sprintf('Invalid composer.json in package file "%s"', $archive->getFilename()));
        }

        $entity = new ApplicationPackage();
        $entity->setName($json['name'])
            ->setVersion($json['version'])
            ->setDocumentRoot(isset($json['extra']['deployment']['docroot'])? $json['extra']['deployment']['docroot'] : null)
            ->setType($this->getTypeName());

        if (isset($json['extra']) && is_array($json['extra'])) {
            $entity->setExtra($json['extra']);
        }

        if (isset($json['extra']['deployment']['parameters']) && is_array($json['extra']['deployment']['parameters'])) {
            $entity->setParameters($this->hydrateParameters($json['extra']['deployment']['parameters']));
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getWebRoot(ApplicationInstance $application)
    {
        // TODO Auto-generated method stub

    }

    /**
     * {@inheritdoc}
     */
    public function install(ApplicationInstance $application)
    {
        // TODO Auto-generated method stub

    }

    /**
     * {@inheritdoc}
     */
    public function remove(ApplicationInstance $application)
    {
        // TODO Auto-generated method stub

    }

    /**
     * {@inheritdoc}
     */
    public function rollback(ApplicationInstance $application)
    {
        // TODO Auto-generated method stub

    }
}
