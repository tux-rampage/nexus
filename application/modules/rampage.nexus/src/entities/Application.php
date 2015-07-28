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

namespace rampage\nexus\entities;

use Doctrine\ODM\MongoDB\Mapping\Annotations as odm;


/**
 * @odm\Document(collection="applications")
 */
class Application
{
    /**
     * @odm\Id
     * @var \MongoId
     */
    private $id = null;

    /**
     * @odm\String
     * @var string
     */
    protected $name = null;

    /**
     * @odm\Bin
     * @var \MongoBinData
     */
    protected $icon = null;

    /**
     * @odm\ReferenceMany(targetDocument="ApplicationPackage", mappedBy="application")
     * @var ApplicationPackage[]
     */
    protected $packages = null;

    /**
     * @odm\String
     * @var string
     */
    protected $provider = null;

    /**
     * @odm\Hash
     * @var array
     */
    protected $providerOptions = [];

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = (string)$name;
        return $this;
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
     * @param ApplicationPackage $package
     * @return self
     */
    public function addPackage($package)
    {
        $this->packages[] = $package;
        return $this;
    }

    /**
     * @return \rampage\nexus\entities\PackageProviderInterface
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param PackageProviderInterface $provider
     * @return self
     */
    public function setProvider(PackageProviderInterface $provider)
    {
        $this->provider = $provider;
        return $this;
    }
}
