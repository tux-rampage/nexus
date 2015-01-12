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

namespace rampage\nexus\json;

use Zend\View\Model\JsonModel;
use Zend\Stdlib\Guard\ArrayOrTraversableGuardTrait;


abstract class AbstractListStragety extends JsonModel
{
    use ArrayOrTraversableGuardTrait;

    /**
     * @var \Traversable|array
     */
    protected $collection;

    /**
     * @param object $item
     * @return AbstractStrategy
     */
    abstract protected function getItemStrategy($item);

    /**
     * {@inheritdoc}
     * @param array|\Traversable $collection
     */
    public function __construct($collection)
    {
        $this->guardForArrayOrTraversable($collection, 'Collection');
        $this->collection = $collection;

        parent::__construct();

        if (is_array($this->collection) || ($this->collection instanceof \Countable)) {
            $this->count = count($this->collection);
        }

        $this->initialize();
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        if (($name == 'items') && !isset($this->variables[$name])) {
            $this->createItems();
        }

        return parent::__get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($name)
    {
        if ($name == 'items') {
            return true;
        }

        return parent::__isset($name);
    }

    /**
     * Intialize model defaults
     */
    protected function initialize()
    {
    }

    /**
     * @return self
     */
    protected function createItems()
    {
        $items = [];

        foreach ($this->collection as $item) {
            $items[] = $this->getItemStrategy($item)->toArray();
        }

        $this->variables['items'] = $items;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariables()
    {
        if (!isset($this->variables['items'])) {
            $this->createItems();
        }

        return parent::getVariables();
    }
}
