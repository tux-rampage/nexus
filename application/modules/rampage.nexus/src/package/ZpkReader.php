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

namespace rampage\nexus\package;

use rampage\nexus\PackageInterface;
use rampage\nexus\entities\PackageParameter;
use Zend\Filter\Boolean;

class ZpkPackage implements PackageInterface
{
    /**
     * @var \SimpleXMLElement
     */
    protected $descriptor;

    /**
     * @var PackageParameter[]
     */
    protected $parameters = null;

    /**
     * @param string $descriptor
     */
    public function __construct(\SimpleXMLElement $descriptor)
    {
        $this->descriptor = $descriptor;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentRoot()
    {
        return (string)$this->descriptor->docroot;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtra($name = null)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return (string)$this->descriptor->name;
    }

    /**
     * Build package parameters
     */
    protected function buildParameters()
    {
        $boolFilter = new Boolean();

        foreach ($this->descriptor->xpath('./parameters/parameter') as $parameterXml) {
            $name = (string)$parameterXml['id'];
            $label = (string)$parameterXml['display'];

        }
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        if ($this->parameters === null) {
            $this->buildParameters();
        }

        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'zpk';
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return $this->descriptor->version;
    }
}
