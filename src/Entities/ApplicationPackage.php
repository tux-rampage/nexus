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

namespace Rampage\Nexus\Entities;

use Rampage\Nexus\Package\PackageInterface;
use Rampage\Nexus\Package\ParameterInterface;
use Rampage\Nexus\Package\ArrayExportableTrait as ArrayExportablePackageTrait;
use Rampage\Nexus\Exception\UnexpectedValueException;

/**
 * Application Package Entity
 */
class ApplicationPackage implements PackageInterface
{
    use ArrayExportablePackageTrait;

    /**
     * @var string
     */
    private $id = null;

    /**
     * The path to the archive
     *
     * @var string
     */
    protected $archive = null;

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var string
     */
    protected $version = null;

    /**
     * @var string
     */
    protected $type = null;

    /**
     * @var string
     */
    protected $documentRoot = null;

    /**
     * @var ParameterInterface[]
     */
    protected $parameters = [];

    /**
     * @var array
     */
    protected $extra = [];

    /**
     * @param PackageInterface $copyFrom
     */
    public function __construct(PackageInterface $package = null)
    {
        if ($package) {
            $this->copy($package);
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Package\PackageInterface::getArchive()
     */
    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * @param string $archive
     * @return self
     */
    public function setArchive($archive)
    {
        $this->archive = $archive;
        return $this;
    }

    /**
     * Copy information from the given package
     *
     * @param PackageInterface $package
     * @return self
     */
    protected function copy(PackageInterface $package)
    {
        if ($this->getType() && ($package->getType() != $this->getType())) {
            throw new UnexpectedValueException('Incompatible package types');
        }

        $this->id = $package->getId();
        $this->archive = $package->getArchive();
        $this->documentRoot = $package->getDocumentRoot();
        $this->extra = $package->getExtra();
        $this->name = $package->getName();
        $this->parameters = [];
        $this->type = $package->getType();
        $this->version = $package->getVersion();

        foreach ($package->getParameters() as $param) {
            $this->parameters[$param->getName()] = $param;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentRoot()
    {
        return $this->documentRoot;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtra($name = null, $default = null)
    {
        if ($name === null) {
            return $this->extra;
        }

        if (!isset($this->extra[$name])) {
            return $default;
        }

        return $this->extra[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return $this->version;
    }
}
