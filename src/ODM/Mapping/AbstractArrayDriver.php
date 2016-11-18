<?php
/**
 * Copyright (c) 2016 Axel Helmert
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
 * @copyright Copyright (c) 2016 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\ODM\Mapping;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver as MappingDriverInterface;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Rampage\Nexus\Exception\InvalidArgumentException;
use Zend\Stdlib\Parameters;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;

/**
 * An abstract array driver
 */
abstract class AbstractArrayDriver implements MappingDriverInterface
{
    const TYPE_SUPERCLASS = 'superclass';
    const TYPE_EMBEDDED = 'embedded';

    /**
     * @var array
     */
    protected $classes = [];

    /**
     * Load all class information as array
     *
     * @return array
     */
    abstract protected function loadData();

    /**
     * Initialize
     */
    protected function init()
    {
        if (!$this->classes) {
            $this->classes = $this->loadData();
        }
    }

    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver::getAllClassNames()
     */
    public function getAllClassNames()
    {
        $this->init();
        return array_keys($this->classes);
    }

    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver::isTransient()
     */
    public function isTransient($className)
    {
        $this->init();
        return !isset($this->classes[$className]);
    }

    /**
     * @param string $className
     * @throws InvalidArgumentException
     * @return Parameters
     */
    protected function getClassData($className)
    {
        $this->init();

        if (!isset($this->classes[$className])) {
            throw new InvalidArgumentException('Could not get mapping data for ' . $className);
        }

        return new Parameters($this->classes[$className]);
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @param string $name
     * @param array $mapping
     */
    protected function mapField(ClassMetadataInfo $metadata, $name, array $mapping)
    {
        $mapping['fieldName'] = $name;
        $metadata->mapField($mapping);
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @param string $name
     * @param array $index
     */
    protected function addIndex(ClassMetadataInfo $metadata, $name, array $index)
    {
        $options = (isset($index['options']) && is_array($index['options']))? $index['options'] : [];
        $keys = $index['keys'];

        if (!isset($options['name'])) {
            $options['name'] = $name;
        }

        $metadata->addIndex($keys, $options);
    }

    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver::loadMetadataForClass()
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        /** @var \Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo $metadata */
        $info = $this->getClassData($className);

        switch ($info->get('type')) {
            case self::TYPE_EMBEDDED:
                $metadata->isEmbeddedDocument = true;
                break;

            case self::TYPE_SUPERCLASS:
                $metadata->isMappedSuperclass = true;
                // Break intentionally omitted

            default:
                if ($repoClass = $info->get('repository')) {
                    $metadata->setCustomRepositoryClass($repoClass);
                }
        }

        $metadata->setCollection($info->get('collection', str_replace('\\', '.', $className)));

        foreach ($info->get('fields', []) as $name => $field) {
            $this->mapField($metadata, $name, $field);
        }

        foreach ($info->get('indexes', []) as $name => $index) {
            $this->addIndex($metadata, $name, $index);
        }
    }
}
