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


/**
 * Implements a binary string data hydration strategy
 */
class BlobStrategy implements StrategyInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\Hydrator\Strategy\StrategyInterface::extract()
     */
    public function extract($value)
    {
        if (!is_resource($value)) {
            $value = stream_get_contents($value);
        }

        return new MongoBinData($value, MongoBinData::GENERIC);
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Hydrator\Strategy\StrategyInterface::hydrate()
     */
    public function hydrate($value)
    {
        if (!is_string($value) && !($value instanceof MongoBinData)) {
            return null;
        }

        $fp = fopen('php://memory', 'w+');
        $fp = fwrite($fp, (string)$value);
        fseek($fp, 0, SEEK_SET);

        return $fp;
    }
}