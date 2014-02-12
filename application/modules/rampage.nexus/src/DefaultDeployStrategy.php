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
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus;

class DefaultDeployStrategy implements DeployStrategyInterface
{
    /**
     * @var string
     */
    protected $webRoot = null;

    /**
     * @var array
     */
    protected $userParams = array();

    /**
     * @var entities\ApplicationInstance
     */
    protected $application = null;

    /**
     * @var string
     */
    protected $dirFormat = '/var/deployment/%appname%/%version%';

    /**
     * @var string
     */
    protected $symlinkFormat = '/var/deployment/%appname%/current';

    /**
     * @see \rampage\nexus\DeployStrategyInterface::activate()
     */
    public function activate()
    {
        // TODO Auto-generated method stub

    }

    /**
     * @see \rampage\nexus\DeployStrategyInterface::completeRemoval()
     */
    public function completeRemoval()
    {
        // TODO Auto-generated method stub

    }

    /**
     * @see \rampage\nexus\DeployStrategyInterface::completeStaging()
     */
    public function completeStaging()
    {
        // TODO Auto-generated method stub

    }

    /**
     * @see \rampage\nexus\DeployStrategyInterface::deactivate()
     */
    public function deactivate()
    {
        // TODO Auto-generated method stub

    }

    /**
     * @see \rampage\nexus\DeployStrategyInterface::getTargetDirectory()
     */
    public function getTargetDirectory()
    {
        // TODO Auto-generated method stub

    }

    /**
     * @see \rampage\nexus\DeployStrategyInterface::prepareRemoval()
     */
    public function prepareRemoval()
    {
        // TODO Auto-generated method stub

    }

    /**
     * @see \rampage\nexus\DeployStrategyInterface::prepareStaging()
     */
    public function prepareStaging()
    {
        mkdir($this->getTargetDirectory(), 0775, true);
        return $this;
    }

    /**
     * @see \rampage\nexus\DeployStrategyInterface::setApplicationInstance()
     */
    public function setApplicationInstance(entities\ApplicationInstance $instance)
    {
        $this->application = $instance;
        return $this;
    }

    /**
     * @see \rampage\nexus\DeployStrategyInterface::setUserParameters()
     */
    public function setUserParameters($parameters)
    {
        $this->userParams = $parameters;
        return $this;
    }

    /**
     * @see \rampage\nexus\DeployStrategyInterface::setWebRoot()
     */
    public function setWebRoot($dir)
    {
        $this->webRoot = $dir;
        return $this;
    }
}
