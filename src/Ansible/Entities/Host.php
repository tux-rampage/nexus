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

use Rampage\Nexus\Entities\Node;
use Rampage\Nexus\Deployment\NodeInterface;
use Rampage\Nexus\Exception\LogicException;
use Rampage\Nexus\Entities\Api\ArrayExchangeInterface;
use Zend\Stdlib\Parameters;
use Rampage\Nexus\Entities\ArrayCollection;
use Rampage\Nexus\Exception\InvalidArgumentException;

class Host implements ArrayExchangeInterface
{
    use VariablesTrait;

    /**
     * The internal entity id
     *
     * @var string
     */
    private $id = null;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var AbstractNode
     */
    protected $node;

    /**
     * @var Group[]|ArrayCollection
     */
    protected $groups;

    /**
     * @var string
     */
    private $defaultNodeType = null;

    /**
     * @var bool
     */
    private $isNodeByGroup = null;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->node = null;
        $this->groups = new ArrayCollection();
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
     * Attach a node to this host
     *
     * @param \Rampage\Nexus\Entities\AbstractNode $node
     * @return self
     */
    public function setNode(NodeInterface $node = null)
    {
        if ($this->node) {
            throw new LogicException('This host already has a deployment node');
        }

        $this->node = $node;
        return $this;
    }

    /**
     * @return multitype:\Rampage\Nexus\Ansible\Entities\Group
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Check for group
     *
     * @param Group $group
     * @return bool
     */
    public function hasGroup(Group $group)
    {
        $predicate = function(Group $item) use ($group) {
            return ($item->getId() == $group->getId());
        };

        return ($this->groups->find($predicate) !== null);
    }

    /**
     * Add a group
     *
     * @param Group $group
     * @return \Rampage\Nexus\Ansible\Entities\Host
     */
    public function addGroup(Group $group)
    {
        if (!$this->hasGroup($group)) {
            $this->groups[] = $group;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExchangeInterface::exchangeArray()
     */
    public function exchangeArray(array $array)
    {
        $data = new Parameters($array);
        $vars = $data->get('variables');
        $groups = $data->get('groups');

        $this->name = $data->get('name', $this->name);

        if (is_array($vars)) {
            $this->setVariables($vars);
        }

        if (is_array($groups)) {
            $this->groups->exchangeArray([]);

            foreach ($groups as $group) {
                if (!$group instanceof Group) {
                    throw new InvalidArgumentException('Groups must only contain group instances, recieved %s', (is_object($group)? get_class($group) : gettype($group)));
                }

                $this->addGroup($group);
            }
        }
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExportableInterface::toArray()
     */
    public function toArray()
    {
        $array = [
            'id' => $this->id,
            'name' => $this->name,
            'groups' => [],
            'variables' => $this->variables,
            'nodeId' => $this->getNode()->getId()
        ];

        foreach ($this->groups as $group) {
            $array['groups'][] = $group->getId();
        }

        return $array;
    }
}
