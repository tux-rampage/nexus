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

namespace Rampage\Nexus\Deployment;

use Rampage\Nexus\Exception\LogicException;
use Zend\ServiceManager\AbstractPluginManager;
use Interop\Container\ContainerInterface;


/**
 * The default node implementation provider
 *
 * This provider will utilize an ioc container to create the prototypes
 */
class NodeStrategyProvider implements NodeStrategyProviderInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $prototypes = [];

    /**
     * Type registry
     *
     * @var array
     */
    protected $types = [];

    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\AbstractPluginManager::__construct()
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->addType(NodeStrategy\Rampage::TYPE_ID, NodeStrategy\Rampage::class);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeProviderInterface::getTypes()
     */
    public function getTypes()
    {
        return array_keys($this->types);
    }

    /**
     * Set the prototype instance
     *
     * @param string $id
     * @param NodeInterface $node
     */
    protected function setPrototype($id, NodeInterface $node)
    {
        $this->prototypes[$id] = $node;
    }

    /**
     * Add a node type
     *
     * @param   string  $id
     * @param   string  $serviceName
     * @return  self
     */
    public function addType($id, $serviceName)
    {
        $this->types[$id] = (string)$serviceName;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Interop\Container\ContainerInterface::get()
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new LogicException('Cannot retrieve non-existend node type: ' . $id);
        }

        if (!$this->prototypes[$id]) {
            $this->prototypes[$id] = $this->container->get($this->types[$id]);
        }

        return clone $this->prototypes[$id];
    }

    /**
     * {@inheritDoc}
     * @see \Interop\Container\ContainerInterface::has()
     */
    public function has($id)
    {
        return isset($this->types[$id]);
    }
}
