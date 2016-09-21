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
use Psr\Log\LoggerTrait;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;


/**
 * Implements a logger thet write to the console
 */
class ConsoleLogger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param OutputInterface $output   Console output implementation
     * @param LoggerInterface $logger   A logger instance to wrap (Tee)
     */
    public function __construct(OutputInterface $output, LoggerInterface $logger = null)
    {
        $this->output = $output;
        $this->logger = $logger?: new NoopLogger();

        if (!$output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow', null, ['bold']);
            $output->getFormatter()->setStyle('warning', $style);
        }
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Log\LoggerInterface::log()
     */
    public function log($level, $message, array $context = array())
    {
        $this->logger->log($level, $message, $context);
        $prefix = '';

        switch ($level) {
            case LogLevel::EMERGENCY: // break intentionally omitted
                $prefix = '[emergency] ';
            case LogLevel::ALERT: // break intentionally omitted
                $prefix = '[alert] ';
            case LogLevel::CRITICAL: // break intentionally omitted
                $prefix = '[critical] ';
            case LogLevel::ERROR:
                $this->output->writeln("<error>{$prefix}{$message}</>");
                break;

            case LogLevel::WARNING:
                $this->output->writeln("<warning>$message</>");
                break;

            case LogLevel::NOTICE:
                $this->output->writeln($message, OutputInterface::OUTPUT_NORMAL | OutputInterface::VERBOSITY_VERBOSE);
                break;

            case LogLevel::INFO:
                $this->output->writeln($message, OutputInterface::OUTPUT_NORMAL | OutputInterface::VERBOSITY_VERY_VERBOSE);
                break;

            case LogLevel::DEBUG:
                $this->output->writeln($message, OutputInterface::OUTPUT_RAW | OutputInterface::VERBOSITY_DEBUG);
                break;

            default:
                $this->output->writeln($message, OutputInterface::OUTPUT_RAW | OutputInterface::VERBOSITY_VERY_VERBOSE);
                break;
        }
    }
}
