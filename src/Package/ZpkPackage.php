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

use Zend\Filter\Boolean;
use SimpleXMLElement;

/**
 * Implementation for ZendServer packages
 */
class ZpkPackage implements PackageInterface
{
    const TYPE_ZPK = 'zpk';

    /**
     * @var \SimpleXMLElement
     */
    protected $descriptor;

    /**
     * @var PackageParameter[]
     */
    protected $parameters = null;

    /**
     * @param SimpleXMLElement $descriptor
     */
    public function __construct(SimpleXMLElement $descriptor)
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
        $this->parameters = [];

        foreach ($this->descriptor->xpath('./parameters/parameter') as $parameterXml) {
            $name = (string)$parameterXml['id'];
            $label = (string)$parameterXml['display']? : $name;
            $type = (string)$parameterXml['type'];
            $readonly = (string)$parameterXml['readonly'];
            $required = (string)$parameterXml['required'];
            $default = (string)$parameterXml->defaultvalue;

            if (!$name || !$type) {
                continue;
            }

            $required = $boolFilter->filter($required);
            $readonly = $boolFilter->filter($readonly);
            $param = new PackageParameter($name);

            $param->setRequired($required)
                ->setDefault($default)
                ->setLabel($label)
                ->addOption('readonly', $readonly);

            switch ($type) {
                case 'choice':
                    $options = [];

                    foreach ($parameterXml->xpath('./validation/enums/enum') as $enum) {
                        $value = (string)$enum;
                        $options[$value] = $value;
                    }

                    $param->setType('select');
                    $param->setOptions('values', $options);
                    break;

                case 'password':
                    $param->setType('password');
                    break;

                case 'email':
                    $param->addOption('validator', 'EmailAddress');
                    break;

                case 'checkbox':
                    $param->setType('checkbox');
                    break;

                case 'number':
                    $param->addOption('validator', 'Number');
                    break;

                case 'hostname':
                    $param->addOption('validator', 'Hostname');
                    break;
            }

            $this->parameters[] = $param;
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
        return self::TYPE_ZPK;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return (string)$this->descriptor->version;
    }

    /**
     * @return string
     */
    public function getAppDir()
    {
        return (string)$this->descriptor->appdir;
    }

    /**
     * @return string
     */
    public function getScriptsDir()
    {
        return (string)$this->descriptor->scriptsdir;
    }
}
