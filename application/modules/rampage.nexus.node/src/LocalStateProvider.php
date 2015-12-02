<?php
/**
 * Copyright (c) 2015 Axel Helmert
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
 * @copyright Copyright (c) 2015 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus\node;

use rampage\nexus\entities\ApplicationInstance;
use rampage\nexus\exceptions;

use Zend\Db\Adapter\AdapterInterface as DbAdapterInterface;
use Zend\Db\Sql\Sql;
use rampage\nexus\node\hydration\LocalStateHydrator;
use rampage\nexus\node\hydration\HydratingArrayCollection;


/**
 * Local state provider
 *
 * This provider will store the most basic app state information in a local database (sqlite)
 */
class LocalStateProvider implements StateProviderInterface
{
    const TABLE_APPLICATIONS = 'applications';
    const TABLE_APPLICATION_PARAMS = 'application_params';

    /**
     * @var DbAdapterInterface
     */
    protected $db;

    /**
     * @var Sql
     */
    protected $sql;

    /**
     * @var LocalStateHydrator
     */
    protected $hydrator;

    /**
     * @var string $archiveDirectory
     */
    protected $archiveDirectory = null;

    /**
     * @param DbAdapterInterface $db
     */
    public function __construct(DbAdapterInterface $db)
    {
        $this->db = $db;
        $this->sql = new Sql($db);
        $this->hydrator = new LocalStateHydrator();
    }

    /**
     * @param string|\SplFileInfo $dir
     * @throws exceptions\RuntimeException
     */
    public function setArchiveDirectory($dir)
    {
        if (!$dir instanceof \SplFileInfo) {
            $dir = new \SplFileInfo($dir);
        }

        if (!$dir->isDir() || !$dir->isWritable()) {
            throw new exceptions\RuntimeException(sprintf('Invalid archive directory "%s". Make sure it exists and is writable.', (string)$dir));
        }

        $this->archiveDirectory = $dir->getPathname();
        return $this;
    }

    /**
     * @param ApplicationInstance $instance
     */
    public function updateApplicationState(ApplicationInstance $instance)
    {
        $update = $this->sql->update(self::TABLE_APPLICATIONS);
        $update->set(['state' => $instance->getState()])
            ->where([ 'id' => $instance->getId() ]);

        $this->sql->prepareStatementForSqlObject($update)->execute();
        return $this;
    }

    /**
     * @see \rampage\nexus\node\StateProviderInterface::getInstalledApplicationPackage()
     */
    public function getInstalledApplicationPackage(ApplicationInstance $instance)
    {
        if (!$this->archiveDirectory) {
            return null;
        }
    }

    /**
     * @param string $id
     * @return array
     */
    protected function loadUserParams($id)
    {
        $params = [];
        $select = $this->sql->select(self::TABLE_APPLICATION_PARAMS);
        $select->where
            ->equalTo('application_id', $id);

        $result = $this->sql->prepareStatementForSqlObject($select)->execute();

        foreach ($result as $item) {
            $key = $item['key'];
            $params[$key] = $item['value'];
        }

        return $params;
    }

    /**
     * @see \rampage\nexus\node\StateProviderInterface::getInstalledApplications()
     */
    public function getInstalledApplications()
    {
        $select = $this->sql->select(self::TABLE_APPLICATIONS)
            ->where
            ->notEqualTo('state', ApplicationInstance::STATE_REMOVED);

        $result = $this->sql->prepareStatementForSqlObject($select)->execute();
        $items = [];

        foreach ($result as $item) {
            $item['userParameters'] = $this->loadUserParams($item['id']);
            $items[] = $item;
        }

        return new HydratingArrayCollection($items, $this->hydrator, new ApplicationInstance());
    }

    /**
     * @see \rampage\nexus\node\StateProviderInterface::publishApplicationState()
     */
    public function publishApplicationState(ApplicationInstance $instance)
    {
        // TODO Auto-generated method stub

    }

    /**
     * @see \rampage\nexus\node\StateProviderInterface::removeApplication()
     */
    public function removeApplication(ApplicationInstance $instance)
    {
        // TODO Auto-generated method stub

    }
}
