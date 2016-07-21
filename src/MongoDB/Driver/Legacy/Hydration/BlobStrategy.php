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

namespace Rampage\Nexus\MongoDB\Driver\Legacy\Hydration;

use Zend\Hydrator\Strategy\StrategyInterface;
use MongoBinData;
use Psr\Http\Message\StreamInterface;
use Zend\Diactoros\Stream;


/**
 * Implements a binary data to stream hydration strategy
 */
class BlobStrategy implements StrategyInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\Hydrator\Strategy\StrategyInterface::extract()
     */
    public function extract($value)
    {
        if ($value instanceof BinDataStream) {
            return $value->getBinData();
        }

        if (!$value instanceof StreamInterface) {
            return null;
        }

        return new MongoBinData($value->getContents(), MongoBinData::GENERIC);
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Hydrator\Strategy\StrategyInterface::hydrate()
     */
    public function hydrate($value)
    {
        if (!$value instanceof MongoBinData) {
            return null;
        }

        return new BinDataStream($value);
    }
}
