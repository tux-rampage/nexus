<?php
/**
 * This is part of rampage-nexus
 * Copyright (c) 2014 Axel Helmert
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
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus\traits;

use rampage\core\Logger;
use Zend\Log\LoggerInterface;
use Zend\Log\Writer\Null as NullLogWriter;


trait LoggerAwareTrait
{
    /**
     * @var \Zend\Logger\LoggerInterface
     */
    protected $logger = null;

	/**
     * {@inheritdoc}
     * @see \Zend\Log\LoggerAwareInterface::setLogger()
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return \Zend\Logger\LoggerInterface
     */
    protected function getLogger()
    {
        if (!$this->logger) {
            $logger = new Logger();
            $logger->addWriter(new NullLogWriter());

            $this->setLogger($logger);
        }

        return $this->logger;
    }
}
