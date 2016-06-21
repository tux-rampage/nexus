<?php
/**
 * Copyright (c) 2015 Axel Helmert
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
 * @copyright Copyright (c) 2015 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\Package;

use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodHydrator;
use Zend\Json\Json;


/**
 * Implementation for composer packages
 */
class ComposerPackage implements PackageInterface
{
    const TYPE_COMPOSER = 'composer';

    /**
     * @var ArrayConfig
     */
    protected $composer;

    /**
     * @var PackageParameter[]
     */
    protected $parameters = null;

    /**
     * @param array|string $json
     */
    public function __construct($json)
    {
        if (is_string($json)) {
            $json = Json::decode($json, Json::TYPE_ARRAY);
        }

        $this->composer = new ArrayConfig($json);
    }

    /**
     * @see \rampage\nexus\PackageInterface::getDocumentRoot()
     */
    public function getDocumentRoot()
    {
        return $this->composer->get('extra.deployment.docroot');
    }

    /**
     * @see \rampage\nexus\PackageInterface::getExtra()
     */
    public function getExtra($name = null)
    {
        if ($name === null) {
            return $this->composer->getSection('extra')->toArray();
        }

        return $this->composer->getSection('extra')->get($name);
    }

    /**
     * @see \rampage\nexus\PackageInterface::getName()
     */
    public function getName()
    {
        return $this->composer->get('name');
    }

    /**
     * @return self
     */
    protected function buildParameters()
    {
        $this->parameters = [];

        foreach ($this->composer->getSection('extra.deployment.parameters') as $name => $param) {
            if (!is_string($name) || ($name == '')) {
                continue;
            }

            $parameter = new PackageParameter($name);

            if (is_string($param)) {
                $parameter->setType($param);
            }

            if ($param instanceof ArrayConfig) {
                $data = $param->toArray();
                unset($data['name']);
                (new ClassMethodHydrator())->hydrate($data, $parameter);
            }

            $this->parameters[] = $parameter;
        }

        return $this;
    }

    /**
     * @see \rampage\nexus\PackageInterface::getParameters()
     */
    public function getParameters()
    {
        if ($this->parameters === null) {
            $this->buildParameters();
        }

        return $this->parameters;
    }

    /**
     * @see \rampage\nexus\PackageInterface::getType()
     */
    public function getType()
    {
        return self::TYPE_COMPOSER;
    }

    /**
     * @see \rampage\nexus\PackageInterface::getVersion()
     */
    public function getVersion()
    {
        return $this->composer->get('version');
    }
}
