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


use Zend\Stdlib\Parameters;
use Rampage\Nexus\Exception\UnexpectedValueException;

/**
 * Vhost definition
 */
class VHost implements Api\ArrayExchangeInterface
{
    /**
     * The name for the default vhost
     */
    const DEFAULT_VHOST = '*';

    /**
     * Regex for valid server names
     */
    const VALID_NAME_REGEX = '~^[a-z0-9_-]+(\.[a-z0-9_-]+)*$~';

    /**
     * @var string
     */
    protected $name;

    /**
     * The default flavour for this host
     *
     * @var string
     */
    protected $flavor = null;

    /**
     * Contains aliases for this vhost
     *
     * @var string[]
     */
    protected $aliases = [];

    /**
     * Flag if SSL should be enabled
     *
     * The result depends on the corresponding flavor.
     *
     * @var bool
     */
    protected $enableSsl = false;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->setName($name);
    }

    /**
     * Checks if this is the default vhost
     *
     * @return boolean
     */
    public function isDefault()
    {
        return ($this->name == self::DEFAULT_VHOST);
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
        if ($name == '') {
            throw new UnexpectedValueException('The VHost name must not be empty');
        }

        if (!preg_match(self::VALID_NAME_REGEX, $name) && ($name != self::DEFAULT_VHOST)) {
            throw new UnexpectedValueException(sprintf('Invalid vhost name: "%s"', $name));
        }

        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getFlavor()
    {
        return $this->flavor;
    }

    /**
     * @param string $flavor
     */
    public function setFlavor($flavor)
    {
        $this->flavor = ($flavor !== null)? (string)$flavor : null;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * @param multitype:\Rampage\Nexus\Entities\string  $aliases
     * @return self
     */
    public function setAliases($aliases)
    {
        $this->clearAliases();

        foreach ($aliases as $alias) {
            $this->addAlias($alias);
        }

        return $this;
    }

    /**
     * Remove all aliases for this vhost
     *
     * @return self
     */
    public function clearAliases()
    {
        $this->aliases = [];
        return $this;
    }

    /**
     * @param string $name
     * @return self
     */
    public function addAlias($name)
    {
        if (!in_array($name, $this->aliases)) {
            $this->aliases[] = $name;
        }

        return $this;
    }

    /**
     * @param string $name
     * @return self
     */
    public function removeAlias($name)
    {
        $offset = array_search($name, $this->aliases);

        if ($offset !== false) {
            unset($this->aliases[$offset]);
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSslEnabled()
    {
        return $this->enableSsl;
    }

    /**
     * @param boolean $enableSsl
     * @return self
     */
    public function setEnableSsl($enableSsl)
    {
        $this->enableSsl = (bool)$enableSsl;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExchangeInterface::exchangeArray()
     */
    public function exchangeArray(array $array)
    {
        $data = new Parameters($array);

        $this->setName($data->get('name'));
        $this->setFlavor($data->get('flavor'));
        $this->setAliases($data->get('aliases'));
        $this->setEnableSsl($data->get('enableSsl'));
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExportableInterface::toArray()
     */
    public function toArray()
    {
        return [
            'name' => $this->name,
            'isDefault' => ($this->name == self::DEFAULT_VHOST),
            'flavor' => $this->flavor,
            'aliases' => $this->aliases,
            'enableSsl' => $this->enableSsl
        ];
    }
}
