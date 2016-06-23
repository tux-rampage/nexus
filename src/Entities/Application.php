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

namespace Rampage\Nexus\Entities;

use Rampage\Nexus\Package\PackageInterface;
use Rampage\Nexus\Exception\UnexpectedValueException;
use Zend\Stdlib\Parameters;


/**
 * Represents a deployable application
 */
class Application implements Api\ArrayExchangeInterface
{
    /**
     * @var mixed
     */
    private $id = null;

    /**
     * The application name
     *
     * @var string
     */
    protected $name = null;

    /**
     * Represents the icon as binary data
     *
     * @var string
     */
    protected $icon = null;

    /**
     * @var ApplicationPackage[]
     */
    protected $packages = [];

    /**
     * Returns the unique identifier of this application
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the application name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return MongoBinData
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param MongoBinData $icon
     * @return self
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return ApplicationPackage[]
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * Find a package by id
     *
     * @param string $packageId
     * @return PackageInterface
     */
    public function findPackage($packageId)
    {
        if (!isset($this->packages[$packageId])) {
            return null;
        }

        return $this->packages[$packageId];
    }

    /**
     * Add a package to this application
     *
     * @param   PackageInterface    $package
     * @return  self
     */
    public function addPackage(PackageInterface $package)
    {
        if ($package->getName() !== $this->getName()) {
            throw UnexpectedValueException::notMatching('Package name', $package->getName(), $this->getName());
        }

        $this->packages[$package->getId()] = $package;
        return $this;
    }

    /**
     * Check if this application has the requested package
     *
     * @param PackageInterface $package
     * @return bool
     */
    public function hasPackage(PackageInterface $package)
    {
        return (isset($this->packages[$package->getId()]));
    }

    /**
     * Exchange entity data with the given array
     *
     * @param array $array
     * @return self
     */
    public function exchangeArray(array $array)
    {
        $params = new Parameters($array);
        $this->name = $params->get('name', $this->name);

        return $this;
    }

    /**
     * Returns the array representation
     *
     * @return array
     */
    public function toArray()
    {
        $array = [
            'id' => $this->id,
            'name' => $this->name,
            'packages' => [],
        ];

        foreach ($this->packages as $package) {
            $array['packages'][] = $package->toArray();
        }
    }
}
