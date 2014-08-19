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

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;


class DeployEvent extends Event implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    const EVENT_DEPLOY = 'deploy';
    const EVENT_REMOVE = 'deploy';

    const EVENT_STAGE = 'stage';
    const EVENT_ACTIVATE = 'activate';
    const EVENT_UNSTAGE = 'unstage';
    const EVENT_DEACTIVATE = 'deactivate';

    /**
     * @var entities\ApplicationInstance
     */
    protected $application = null;

    /**
     * @var DeployStrategyInterface
     */
    protected $deployStrategy = null;

    /**
     * @var WebConfigInterface
     */
    protected $webConfig = null;

    /**
     * @var package\ApplicationPackageInterface
     */
    protected $package = null;

    /**
     * @param  package\ApplicationPackageInterface $package
     * @return self
     */
    public function setPackage(package\ApplicationPackageInterface $package)
    {
        $this->package = $package;
        return $this;
    }

    /**
     * @return package\ApplicationPackageInterface
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @return \rampage\nexus\entities\ApplicationInstance
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param \rampage\nexus\entities\ApplicationInstance $application
     * @return self
     */
    public function setApplication(entities\ApplicationInstance $application)
    {
        $this->application = $application;
        return $this;
    }

    /**
     * @return \rampage\nexus\DeployStrategyInterface
     */
    public function getDeployStrategy()
    {
        return $this->deployStrategy;
    }

    /**
     * @param \rampage\nexus\DeployStrategyInterface $deployStrategy
     * @return self
     */
    public function setDeployStrategy(DeployStrategyInterface $deployStrategy)
    {
        $this->deployStrategy = $deployStrategy;
        return $this;
    }

    /**
     * @return \rampage\nexus\WebConfigInterface
     */
    public function getWebConfig()
    {
        return $this->webConfig;
    }

    /**
     * @param \rampage\nexus\WebConfigInterface $webConfig
     * @return self
     */
    public function setWebConfig(WebConfigInterface $webConfig)
    {
        $this->webConfig = $webConfig;
        return $this;
    }
}
