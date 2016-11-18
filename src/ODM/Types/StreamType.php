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

namespace Rampage\Nexus\ODM\Types;

use Doctrine\ODM\MongoDB\Types\Type;
use Psr\Http\Message\StreamInterface;
use Rampage\Nexus\ODM\BinDataStream;

class StreamType extends Type
{
    /**
     * {@inheritDoc}
     * @see \Doctrine\ODM\MongoDB\Types\Type::closureToPHP()
     */
    public function closureToPHP()
    {
        return '$return = ($value instanceof \MongoBinData)? new \Rampage\Nexus\ODM\BinDataStream($value) : null;';
    }

    /**
     * {@inheritDoc}
     * @see \Doctrine\ODM\MongoDB\Types\Type::closureToMongo()
     */
    public function closureToMongo()
    {
        return '$return = ($value instanceof \Psr\Http\Message\StreamInterface)? new \MongoBinData($value->getContents()) : null;';
    }

    /**
     * {@inheritDoc}
     * @see \Doctrine\ODM\MongoDB\Types\Type::convertToDatabaseValue()
     */
    public function convertToDatabaseValue($value)
    {
        if (!$value instanceof StreamInterface) {
            return null;
        }

        return new \MongoBinData($value->getContents());
    }

    /**
     * {@inheritDoc}
     * @see \Doctrine\ODM\MongoDB\Types\Type::convertToPHPValue()
     */
    public function convertToPHPValue($value)
    {
        if (!$value instanceof \MongoBinData) {
            return null;
        }

        return new BinDataStream($value);
    }
}
