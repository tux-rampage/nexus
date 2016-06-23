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

use Rampage\Nexus\Exception\InvalidArgumentException;
use Rampage\Nexus\Exception\UnexpectedValueException;
use Rampage\Nexus\Entities\PackageParameter;


/**
 * Implementation for composer packages
 */
class ComposerPackage implements PackageInterface
{
    use ArrayExportableTrait;
    use BuildIdAwareTrait;

    /**
     * Composer package type constant
     */
    const TYPE_COMPOSER = 'composer';

    /**
     * Data of composer.json
     *
     * @var array
     */
    protected $data;

    /**
     * @var PackageParameter[]
     */
    protected $parameters = null;

    /**
     * @var string
     */
    protected $archive = null;

    /**
     * @param array|string $json
     */
    public function __construct($json)
    {
        if (!is_string($json)) {
            $json = json_decode($json, true);
        }

        if (!is_array($json)) {
            throw new InvalidArgumentException('The composer.json must be an array or a string containing valid json');
        }

        $this->data = $json;
        $this->validate();
        $this->setBuildId(null);
    }

    /**
     * @throws UnexpectedValueException
     */
    protected function validate()
    {
        $requiredFields = ['name', 'version'];

        foreach ($requiredFields as $field) {
            if (!isset($this->data[$field]) || ($this->data[$field] == '')) {
                throw new UnexpectedValueException('Missing field in composer.json: ' . $field);
            }
        }
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
     * {@inheritDoc}
     * @see \Rampage\Nexus\Package\PackageInterface::getId()
     */
    public function getId()
    {
        return $this->buildId;
    }

    /**
     * Sets the archive file
     *
     * @param string $archive
     * @return self
     */
    public function setArchive($archive)
    {
        $this->archive = $archive? : null;
        return $this;
    }

    /**
     * Returns the configured document root
     *
     * @return string
     */
    public function getDocumentRoot()
    {
        if (!isset($this->data['extra']['deployment']['docroot'])) {
            return null;
        }

        return (string)$this->data['extra']['deployment']['docroot'];
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Package\PackageInterface::getExtra()
     */
    public function getExtra($name = null)
    {
        if (!isset($this->data['extra'])) {
            return null;
        }

        if ($name !== null) {
            return isset($this->data['extra'][$name])? $this->data['extra'][$name] : null;
        }

        return $this->data['extra'];
    }

    /**
     * Returns the package name
     *
     * @return string
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

        if (!isset($this->data['extra']['deployment']['parameters'])
            || !is_array($this->data['extra']['deployment']['parameters'])) {
            return;
        }

        foreach ($this->data['extra']['deployment']['parameters'] as $name => $param) {
            if (!is_string($name) || ($name == '')) {
                continue;
            }

            $parameter = new PackageParameter($name);

            if (is_string($param)) {
                $parameter->setType($param);
            } else {
                $parameter->exchangeArray($param);
            }

            $this->parameters[$parameter->getName()] = $parameter;
        }
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
        return $this->data['version'];
    }
}
