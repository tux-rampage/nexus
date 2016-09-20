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

namespace Rampage\Nexus\BuildSystem\Jenkins\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Rampage\Nexus\BuildSystem\Jenkins\PackageScanner\PackageScannerInterface;
use Rampage\Nexus\BuildSystem\Jenkins\Repository\InstanceRepositoryInterface;
use Psr\Log\LoggerAwareInterface;
use Rampage\Nexus\ConsoleLogger;

/**
 * Implementation of the scan command
 */
class ScanCommand extends Command
{
    /**
     * @var PackageScannerInterface|LoggerAwareInterface
     */
    private $scanner;

    /**
     * @var InstanceRepositoryInterface
     */
    private $repository;

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::__construct()
     */
    public function __construct(PackageScannerInterface $scanner, InstanceRepositoryInterface $repository)
    {
        parent::__construct('jenkins:scan');
        $this->scanner = $scanner;
        $this->repository = $repository;
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setDescription('Scans all configured jenkins instances')
            ->setHelp('Scans all configured jenkins instances für builds with deployable packages in their artifacts.');
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $instances = $this->repository->findAll();
        $errors = 0;

        if (!count($instances)) {
            $output->writeln('There are no jenkins instances to scan.');
            return 1;
        }

        if ($this->scanner instanceof LoggerAwareInterface) {
            $logger = (method_exists($this->scanner, 'getLogger'))? $this->scanner->getLogger() : null;
            $this->scanner->setLogger(new ConsoleLogger($output, $logger));
        }

        $output->writeln('<info>Scanning jenkins instances for artifacts ...</>');
        foreach ($instances as $instance) {
            $output->writeln("Scanning jenkins instance {$instance->getId()} ...");

            try {
                $this->scanner->scan($instance);
            } catch (\Throwable $e) {
                $this->getApplication()->renderException($e, $output);
                $errors++;
            }
        }

        $output->writeln('Scan completed.');
        return $errors? 2 : 0;
    }
}
