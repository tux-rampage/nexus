<?php
/**
 * This is part of rampage-nexus
 * Copyright (c) 2013 Axel Helmert
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
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus\entities;

use Doctrine\ORM\Mapping as orm;

/**
 * @orm\Entity
 */
class ApplicationEntity
{
    const STATE_DEPLOYED = 'deployed';
    const STATE_PENDING = 'pending';
    const STATE_STAGING = 'staging';
    const STATE_ACTIVATING = 'activating';
    const STATE_REMOVING = 'removing';
    const STATE_DEACTIVATING = 'deactivating';

    /**
     * @orm\Id @orm\Column(type="integer") @orm\GeneratedValue
     * @var int
     */
    protected $id = null;

    protected $name = null;

    protected $state = null;

    /**
     * @var string
     */
    protected $icon = null;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }
}
