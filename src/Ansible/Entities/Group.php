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

namespace Rampage\Nexus\Ansible\Entities;

use Rampage\Nexus\Entities\Api\ArrayExchangeInterface;

class Group implements ArrayExchangeInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var Group[]
     */
    protected $children = [];

    /**
     * @var array
     */
    protected $variables = [];

    /**
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
        $this->label = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return self
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return multitype:
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param array $variables
     * @return self
     */
    public function setVariables(array $variables)
    {
        $this->variables = $variables;
        return $this;
    }

    /**
     * @return multitype:\Rampage\Nexus\Ansible\Entities\Group
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param Group $group
     * @return self
     */
    public function addChild(Group $group)
    {
        $this->children[$group->getId()] = $group;
        return $this;
    }

    /**
     * @param Group $group
     * @return \Rampage\Nexus\Ansible\Entities\Group
     */
    public function removeChild(Group $group)
    {
        unset($this->children[$group->getId()]);
        return $this;
    }

    /**
     * @return boolean
     */
    public function hasVariables()
    {
        return !empty($this->variables);
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return (count($this->children) > 0);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExchangeInterface::exchangeArray()
     */
    public function exchangeArray(array $array)
    {
        if (isset($array['label'])) {
            $this->label = (string)$array['label'];
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExportableInterface::toArray()
     */
    public function toArray()
    {
        $data = [
            'id' => $this->id,
            'label' => $this->label,
            'children' => []
        ];

        foreach ($this->children as $child) {
            $data['children'][] = $child->getId();
        }

        return $data;
    }
}