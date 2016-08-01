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

namespace Rampage\Nexus\Ansible;

use Rampage\Nexus\Ansible\Repository\HostRepositoryInterface;
use Rampage\Nexus\Ansible\Repository\GroupRepositoryInterface;

class InventoryProvider
{
    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepo;

    /**
     * @var HostRepositoryInterface
     */
    private $hostRepo;

    /**
     * Builds the ansible inventory list structure
     *
     * @return array
     */
    public function list()
    {
        $list = [];

        /* @var $host Entities\Host */
        foreach ($this->hostRepo->findAll() as $host) {
            if ($host->hasVariables()) {
                $list['_meta']['hostvars'][$host->getName()] = $host->getVariables();
            }

            $list['all'][] = $host->getName();
        }

        /* @var $group Entities\Group */
        foreach ($this->groupRepo->findAll() as $group) {
            $hosts = $this->hostRepo->findByGroup($group);

            foreach ($hosts as $host) {
                $list[$group->getId()]['hosts'][] = $host->getName();
            }

            if ($hosts->hasVariables()) {
                $list[$group->getId()]['vars'] = $group->getVariables();
            }

            foreach ($group->getChildren() as $child) {
                $list[$group->getId()]['children'][] = $child->getId();
            }
        }

        return $list;
    }

    /**
     * Returns the host's variables
     *
     * @param string $name
     * @return array
     */
    public function host($name)
    {
        /* @var $host Entities\Host */
        $host = $this->hostRepo->findOne($name);

        if (!$host) {
            return [];
        }

        return $host->getVariables();
    }
}