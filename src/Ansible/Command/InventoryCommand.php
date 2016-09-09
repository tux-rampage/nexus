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

namespace Rampage\Nexus\Ansible\Command;

use Rampage\Nexus\Ansible\InventoryProviderInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class InventoryCommand extends Command
{
    /**
     * @var InventoryProviderInterface
     */
    private $inventoryProvider;

    /**
     * @param InventoryProviderInterface $inventoryProvider
     */
    public function __construct(InventoryProviderInterface $inventoryProvider)
    {
        $this->inventoryProvider = $inventoryProvider;
        parent::__construct('ansible:inventory');
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setDescription('Provides the inventory for ansible')
            ->setHelp('Provides the groups and hostvars hash for as described in the ansible documentation.')
            ->addOption('list', null, InputOption::VALUE_NONE, 'Create the input listing')
            ->addOption('host', null, InputOption::VALUE_NONE, 'Get the host variables hash')
            ->addArgument('hostname', InputArgument::OPTIONAL, 'The hostname when using --host');
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('host')) {
            $hostname = $input->getArgument('hostname');

            if (!$hostname) {
                $output->writeln('<error>Missing hostname</error>');
                return 7;
            }

            $data = $this->inventoryProvider->host($hostname);
        } else {
            $data = $this->inventoryProvider->listInventory();
        }

        $output->write(json_encode($data, JSON_PRETTY_PRINT), true, OutputInterface::OUTPUT_RAW);
    }
}
