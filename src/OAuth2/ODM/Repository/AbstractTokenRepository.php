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

namespace Rampage\Nexus\OAuth2\ODM\Repository;

use Rampage\Nexus\ODM\Repository\AbstractRepository;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Abstract token repository implementation
 */
abstract class AbstractTokenRepository
{
    /**
     * @var DocumentManager
     */
    protected $objectManager = null;

    /**
     * @param DocumentManager $objectManager
     */
    public function __construct(DocumentManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\ODM\Repository\AbstractRepository::getEntityClass()
     */
    abstract protected function getEntityClass();

    /**
     * @return \Doctrine\MongoDB\Collection
     */
    protected function getCollection()
    {
        return $this->objectManager->getDocumentCollection($this->getEntityClass());
    }

    /**
     * Revoke the given token id
     *
     * @param string $id
     */
    protected function revokeById($id)
    {
        $this->getCollection()->update(['_id' => $id], [
            '$set' => [
                'revoked' => true
            ]
        ]);
    }

    /**
     * Check revocation
     *
     * @param string $id
     * @return boolean
     */
    protected function isRevoked($id)
    {
        $token = $this->objectManager->find($this->getEntityClass(), $id);
        return ($token && $token->isRevoked());
    }
}
