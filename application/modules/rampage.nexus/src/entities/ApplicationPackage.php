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

namespace rampage\nexus\entities;

use rampage\nexus\exceptions;
use rampage\nexus\PackageInterface;

use Doctrine\ODM\MongoDB\Mapping\Annotations as odm;
use Doctrine\MongoDB\GridFSFile;

/**
 * @odm\Document(collection="packages")
 */
class ApplicationPackage implements PackageInterface
{
    /**
     * @odm\Id
     * @var \MongoId
     */
    private $id = null;

    /**
     * @odm\Field(type="string")
     * @var string
     */
    protected $filename = null;

    /**
     * @odm\ReferenceOne(targetDocument="PakageFile", simple=true)
     * @var PackageFile|null
     */
    protected $file = null;

    /**
     * @odm\Field(type="string")
     * @var string
     */
    protected $name = null;

    /**
     * @odm\Field(type="string")
     * @var string
     */
    protected $version = null;

    /**
     * @odm\Field(type="string")
     * @var string
     */
    protected $type = null;

    /**
     * @odm\Field(type="string")
     * @var string
     */
    protected $documentRoot = null;

    /**
     * @odm\EmbedMany(targetDocument="PackageParameter")
     * @var PackageParameter[]
     */
    protected $parameters = [];

    /**
     * @odm\Hash
     * @var array
     */
    protected $extra = [];

    /**
     * @return \MongoId
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Copy information from the given package
     *
     * @param PackageInterface $package
     * @return self
     */
    public function copy(PackageInterface $package)
    {
        if ($this->getType() && ($package->getType() != $this->getType())) {
            throw new exceptions\RuntimeException('Incompatible package types');
        }

        $this->setDocumentRoot($package->getDocumentRoot())
            ->setExtra($package->getExtra())
            ->setName($package->getName())
            ->setParameters($package->getParameters())
            ->setVersion($package->getVersion())
            ->setType($package->getType());

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
     * @param string $documentRoot
     * @return self
     */
    public function setDocumentRoot($documentRoot)
    {
        $this->documentRoot = $documentRoot;
        return $this;
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
     * @param array|string $extra
     * @param mixed $value
     * @return self
     */
    public function setExtra($extra, $value = null)
    {
        if (is_array($extra)) {
            $this->extra = $extra;
        } else {
            $this->extra[$extra] = $value;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
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
        $this->name = $name;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param multitype:\rampage\nexus\entities\PackageParameter  $parameters
     * @return self
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = (string)$type;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     * @return self
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return \Doctrine\MongoDB\GridFSFile
     */
    public function getFile()
    {
        if (!$this->file instanceof GridFSFile) {
            return null;
        }

        return $this->file;
    }

    /**
     * @param \SplFileInfo|string $file
     * @return self
     */
    public function setFile($file)
    {
        if ($file instanceof \SplFileInfo) {
            $file = $file->getPathname();
        }

        $this->filename = basename($file);
        $this->file = $file;

        return $this;
    }
}
