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

namespace Rampage\Nexus\Persistence\Predicate;

use Rampage\Nexus\Exception\UnexpectedValueException;

/**
 * Default predicate set implementation
 */
class PredicateSet implements PredicateSetInterface
{
    /**
     * @var string
     */
    private $combination;

    /**
     * @var PredicateInterface[]
     */
    private $predicates = [];

    /**
     * @param string $combination
     */
    public function __construct($combination = self::COMBINE_BY_AND)
    {
        $this->setCombination($combination);
    }

    /**
     * @param field_type $combination
     * @return self
     */
    public function setCombination($combination)
    {
        $combination = strtolower($combination);
        $allowed = [ self::COMBINE_BY_AND, self::COMBINE_BY_OR ];

        if (!in_array($combination, $allowed)) {
            throw UnexpectedValueException::notInSet('combination', $combination, $allowed);
        }

        $this->combination = $combination;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Persistence\Predicate\PredicateSetInterface::getCombination()
     */
    public function getCombination()
    {
        return $this->combination;
    }

    /**
     * Add a predicate
     *
     * @param   PredicateInterface $predicate
     * @return  self
     */
    public function addPredicate(PredicateInterface $predicate)
    {
        $this->predicates[] = $predicate;
        return $this;
    }

    /**
     * Remove all predicates
     *
     * @return self
     */
    public function clear()
    {
        $this->predicates = [];
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Persistence\Predicate\PredicateSetInterface::getPredicates()
     */
    public function getPredicates()
    {
        return $this->predicates;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Persistence\Predicate\PredicateSetInterface::hasPredicates()
     */
    public function hasPredicates()
    {
        $accepted = array_filter($this->predicates, function($p) {
            return ($p instanceof PredicateSetInterface)? $p->hasPredicates() : true;
        });

        return (count($accepted) > 0);
    }
}
