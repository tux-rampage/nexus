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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Axel Helmert
 * @copyright Copyright (c) 2016 Axel Helmert
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\ODM;

use Rampage\Nexus\Exception\LogicException;
use Psr\Http\Message\StreamInterface;
use Zend\Diactoros\Stream;

/**
 * Implements a binary data stream
 */
final class BinDataStream implements StreamInterface
{
    /**
     * The underlying bindata
     *
     * @var \MongoBinData
     */
    private $binData;

    /**
     * Utilized stream Resource
     *
     * @var Stream
     */
    private $stream = null;

    /**
     *
     * @param \MongoBinData $binData
     */
    public function __construct(\MongoBinData $binData)
    {
        $this->binData = $binData;
    }

    /**
     *
     * @return MongoBinData
     */
    public function getBinData()
    {
        return $this->binData;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Psr\Http\Message\StreamInterface::__toString()
     */
    public function __toString()
    {
        return $this->binData->__toString();
    }

    /**
     *
     * @throws \LogicException
     * @return \Zend\Diactoros\Stream
     */
    private function wrap()
    {
        if (! $this->stream) {
            $fp = fopen('php://temp', 'r+');
            fwrite($fp, $this->binData->__toString());
            $this->stream = new Stream($fp);
        }

        return $this->stream;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Psr\Http\Message\StreamInterface::close()
     */
    public function close()
    {
        $this->wrap()->close();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Psr\Http\Message\StreamInterface::detach()
     */
    public function detach()
    {
        return $this->wrap()->detach();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Psr\Http\Message\StreamInterface::eof()
     */
    public function eof()
    {
        return $this->wrap()->eof();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Psr\Http\Message\StreamInterface::getContents()
     */
    public function getContents()
    {
        return $this->__toString();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Psr\Http\Message\StreamInterface::getMetadata()
     */
    public function getMetadata($key = null)
    {
        $meta = $this->wrap()->getMetadata();
        $meta['mode'] = 'r';
        if ($key !== null) {
            return isset($meta[$key])? $meta[$key] : null;
        }
        return $meta;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Psr\Http\Message\StreamInterface::getSize()
     */
    public function getSize()
    {
        return $this->wrap()->getSize();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Psr\Http\Message\StreamInterface::isReadable()
     */
    public function isReadable()
    {
        return $this->wrap()->isReadable();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Psr\Http\Message\StreamInterface::isSeekable()
     */
    public function isSeekable()
    {
        return $this->wrap()->isSeekable();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Psr\Http\Message\StreamInterface::isWritable()
     */
    public function isWritable()
    {
        return false;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Psr\Http\Message\StreamInterface::read()
     */
    public function read($length)
    {
        return $this->wrap()->read($length);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Psr\Http\Message\StreamInterface::rewind()
     */
    public function rewind()
    {
        return $this->wrap()->rewind();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Psr\Http\Message\StreamInterface::seek()
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        return $this->wrap()->seek($offset, $whence);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Psr\Http\Message\StreamInterface::tell()
     */
    public function tell()
    {
        return $this->wrap()->tell();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Psr\Http\Message\StreamInterface::write()
     */
    public function write($string)
    {
        throw new LogicException('Stream is not writable');
    }
}
