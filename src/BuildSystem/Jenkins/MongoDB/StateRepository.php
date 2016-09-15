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

namespace Rampage\Nexus\BuildSystem\Jenkins\MongoDB;

use Rampage\Nexus\BuildSystem\Jenkins\Repository\StateRepositoryInterface;
use Rampage\Nexus\BuildSystem\Jenkins\PackageScanner\InstanceConfig;
use Rampage\Nexus\BuildSystem\Jenkins\Build;
use Rampage\Nexus\BuildSystem\Jenkins\Job;

use Rampage\Nexus\MongoDB\Driver\DriverInterface;
use Rampage\Nexus\MongoDB\Driver\CollectionInterface;

/**
 * Implements the state repository using MongoDB
 */
final class StateRepository implements StateRepositoryInterface
{
    /**
     * @var CollectionInterface
     */
    private $collection;

    /**
     * @param DriverInterface $driver
     */
    public function __construct(DriverInterface $driver)
    {
        $this->collection = $driver->getCollection('JenkinsImportStates');
    }

    /**
     * Builds the identifier
     *
     * @param InstanceConfig $config
     * @param Job $job
     * @return string[][]
     */
    private function buildId(InstanceConfig $config, Job $job)
    {
        return [
            'i' => $config->getId(),
            'j' => $job->getFullName()
        ];
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\BuildSystem\Jenkins\Repository\StateRepositoryInterface::addProcessedBuild()
     */
    public function addProcessedBuild(InstanceConfig $config, Build $build)
    {
        $data = [ '_id' => $this->buildId($config, $build->getJob()) ];
        $this->collection->update($data, [
            '$addToSet' => [
                'processedBuilds' => (string)$build->getId()
            ]
        ], false, true);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\BuildSystem\Jenkins\Repository\StateRepositoryInterface::getProcessedBuilds()
     */
    public function getProcessedBuilds(InstanceConfig $config, Job $job)
    {
        $query = [ '_id' => $this->buildId($config, $job) ];
        $data = $this->collection->findOne($query);

        if ($data && isset($data['processedBuilds'])) {
            return $data['processedBuilds'];
        }

        return [];
    }
}
