<?php
/**
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

namespace rampage\nexus;

use Zend\EventManager\Event;

class DeployEvent extends Event
{
    const PRE_STAGE = 'preStage';
    const POST_STAGE = 'postStage';
    const PRE_ACTIVATE = 'preActivate';
    const POST_ACTIVATE = 'postActivate';
    const PRE_UNSTAGE = 'preUnstage';
    const POST_UNSTAGE = 'postUnstage';
    const PRE_DEACTIVATE = 'preDeactivate';
    const POST_DEACTIVATE = 'postDeactivate';

    /**
     * @var PackageInstallerInterface
     */
    private $package = null;

    /**
     * {@inheritdoc}
     * @see \Zend\EventManager\Event::getTarget()
     * @return entities\ApplicationInstance
     */
    public function getTarget()
    {
        return parent::getTarget();
    }

    /**
     * @param PackageInstallerInterface $package
     * @return self
     */
    public function setPackage(PackageInstallerInterface $package)
    {
        $this->package = $package;
        return $this;
    }

    /**
     * @return PackageInstallerInterface
     */
    public function getPackage()
    {
        return $this->package;
    }
}
