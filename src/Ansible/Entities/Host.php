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

use Rampage\Nexus\Entities\AbstractNode;

class Host
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var AbstractNode
     */
    protected $node;

    /**
     * @var array
     */
    protected $variables = [];

    /**
     * @var Group[]
     */
    protected $groups = [];

    /**
     * @var string
     */
    private $defaultNodeType = null;

    private $isNodeByGroup = null;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->node = null;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the node type by assigned groups
     *
     * @return bool
     */
    public function getDefaultNodeType()
    {
        if ($this->isNodeByGroup === null) {
            $this->defaultNodeType = null;
            $this->isNodeByGroup = false;

            foreach ($this->groups as $group) {
                $this->defaultNodeType = $group->getDeploymentType();

                if ($this->defaultNodeType) {
                    $this->isNodeByGroup = true;
                    break;
                }
            }
        }

        return $this->defaultNodeType;
    }

    /**
     * @return \Rampage\Nexus\Entities\AbstractNode
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @return boolean
     */
    public function hasVariables()
    {
        return !empty($this->variables);
    }

    /**
     * @return multitype:
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @return multitype:\Rampage\Nexus\Ansible\Entities\Group
     */
    public function getGroups()
    {
        return $this->groups;
    }
}
