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

namespace Rampage\Nexus;

use Psr\Log\LoggerInterface;

/**
 * A logger that does nothing
 */
class NoopLogger implements LoggerInterface
{
    /**
     * {@inheritDoc}
     * @see \Psr\Log\LoggerInterface::alert()
     */
    public function alert($message, array $context = array())
    {
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Log\LoggerInterface::critical()
     */
    public function critical($message, array $context = array())
    {
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Log\LoggerInterface::debug()
     */
    public function debug($message, array $context = array())
    {
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Log\LoggerInterface::emergency()
     */
    public function emergency($message, array $context = array())
    {
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Log\LoggerInterface::error()
     */
    public function error($message, array $context = array())
    {
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Log\LoggerInterface::info()
     */
    public function info($message, array $context = array())
    {
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Log\LoggerInterface::log()
     */
    public function log($level, $message, array $context = array())
    {
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Log\LoggerInterface::notice()
     */
    public function notice($message, array $context = array())
    {
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Log\LoggerInterface::warning()
     */
    public function warning($message, array $context = array())
    {
    }
}
