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

namespace Rampage\Nexus\MongoDB\Hydration\EntityHydrator;

use Rampage\Nexus\MongoDB\Hydration\ReflectionHydrator;
use Rampage\Nexus\MongoDB\Driver\DriverInterface;
use Rampage\Nexus\MongoDB\Hydration\CollectionStrategy;
use Rampage\Nexus\MongoDB\Hydration\EmbeddedStrategy;
use Rampage\Nexus\Entities\VHost;
use Rampage\Nexus\MongoDB\Hydration\ImmutableCollectionStrategy;
use Rampage\Nexus\MongoDB\EmptyCursor;
use Rampage\Nexus\MongoDB\Repository\NodeRepository;
use Rampage\Nexus\Entities\ApplicationInstance;
use Rampage\Nexus\Exception\LogicException;
use Rampage\Nexus\MongoDB\CursorInterface;
use Rampage\Nexus\Repository\ApplicationRepositoryInterface;
use Rampage\Nexus\Repository\NodeRepositoryInterface;

/**
 * Hydrator for deploy targets
 */
class DeployTargetHydrator extends ReflectionHydrator
{
    /**
     * @var object[]
     */
    private static $prototypes = [];

    /**
     * @var NodeRepositoryInterface
     */
    private $nodeRepository = null;

    /**
     * @return VHost|ApplicationInstance
     */
    protected static function getPrototype($class)
    {
        if (!isset(self::$prototypes[$class])) {
            self::$prototypes[$class] = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
        }

        return self::$prototypes[$class];
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Hydration\ReflectionHydrator::__construct()
     */
    public function __construct(DriverInterface $driver, ApplicationRepositoryInterface $applicationRepository)
    {
        parent::__construct([
            'name',
            'vhosts',
            'defaultVhost',
            'nodes',
            'applications',
        ], 'id');

        $vhostStrategy = new EmbeddedStrategy(self::getPrototype(VHost::class), new VHostHydrator($driver));
        $appStrategy = new EmbeddedStrategy(self::getPrototype(ApplicationInstance::class), new ApplicationInstanceHydrator($driver, $applicationRepository));

        $this->addStrategy('id', $driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_ID));
        $this->addStrategy('name', $driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_STRING));
        $this->addStrategy('vhosts', new CollectionStrategy($vhostStrategy, true, VHost::class));
        $this->addStrategy('defaultVhost', $vhostStrategy);
        $this->addStrategy('nodes', new ImmutableCollectionStrategy(function($value, &$data) {
            return $this->createNodesCursor($data);
        }));

        $this->addStrategy('applications', new CollectionStrategy($appStrategy, true));
    }

    /**
     * @param NodeRepository $nodeRepository
     * @return \Rampage\Nexus\MongoDB\Hydration\EntityHydrator\DeployTargetHydrator
     */
    public function setNodeRepository(NodeRepositoryInterface $nodeRepository)
    {
        $this->nodeRepository = $nodeRepository;
        return $this;
    }

    /**
     * @param   array           $data   The original hydration data
     * @throws  LogicException          When there is no repository for getting the nodes
     * @return  CursorInterface         The resulting cursor
     */
    private function createNodesCursor(&$data)
    {
        if (!$this->nodeRepository) {
            throw new LogicException('Cannot provide cursor to node instances without a node repository');
        }

        if (!isset($data[self::HYDRATION_CONTEXT_KEY])) {
            return new EmptyCursor();
        }

        return $this->nodeRepository->findByTarget($data[self::HYDRATION_CONTEXT_KEY]);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Hydration\ReflectionHydrator::hydrate()
     */
    public function hydrate(array $data, $object)
    {
        // Ensure applications hydrated last
        if (isset($data['applications'])) {
            $value = $data['applications'];
            unset($data['applications']);
            $data['applications'] = $value;
        }

        return parent::hydrate($data, $object);
    }
}
