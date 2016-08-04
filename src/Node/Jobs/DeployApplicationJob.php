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

namespace Rampage\Nexus\Node\Job;

use Rampage\Nexus\Job\JobInterface;
use Rampage\Nexus\Job\ContainerAwareInterface;
use Rampage\Nexus\Job\ContainerAwareTrait;

use Rampage\Nexus\Node\Entities\StatefulApplicationInstance;
use Rampage\Nexus\Node\DeployStrategyInterface;
use Rampage\Nexus\Node\Repository\ApplicationRepositoryInterface;

use Rampage\Nexus\Exception\LogicException;
use Rampage\Nexus\Exception\RuntimeException;

use Throwable;

/**
 * Implementation for deploy application jobs
 */
class DeployApplicationJob implements JobInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var StatefulApplicationInstance
     */
    protected $application;

    /**
     * @param StatefulApplicationInstance $application
     */
    public function __construct(StatefulApplicationInstance $application)
    {
        $this->application = $application;
    }

    /**
     * @return DeployStrategyInterface
     */
    protected function getDeployStrategy()
    {
        if (!$this->container) {
            throw new LogicException('Cannot obtain deploy strategy without an ioc container');
        }

        return $this->container->get(DeployStrategyInterface::class);
    }

    /**
     * @return ApplicationRepositoryInterface
     */
    protected function getApplicationRepository()
    {
        if (!$this->container) {
            throw new LogicException('Cannot obtain the application repository without an ioc container');
        }

        return $this->container->get(ApplicationRepositoryInterface::class);
    }


    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Jobs\JobInterface::getPriority()
     */
    public function getPriority()
    {
        return 100;
    }

    /**
     * @param string $state
     */
    protected function changeApplicationState($state)
    {
        $this->application->setState($state);
        $this->getApplicationRepository()->updateState($this->application);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Jobs\JobInterface::run()
     */
    public function run()
    {
        $instance = $this->application;
        $strategy = $this->getDeployStrategy();

        try {
            $this->changeApplicationState(StatefulApplicationInstance::STATE_STAGING);
            $strategy->stage($instance);

            $this->changeApplicationState(StatefulApplicationInstance::STATE_ACTIVATING);
            $strategy->prepareActivation($instance);
            $strategy->activate($instance);

            $instance->setDeployedPackage($instance->getPackage());
            $this->changeApplicationState(StatefulApplicationInstance::STATE_DEPLOYED);
        } catch (Throwable $e) {
            $this->changeApplicationState(StatefulApplicationInstance::STATE_ERROR);
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     * @see Serializable::serialize()
     */
    public function serialize()
    {
        return (string)$this->application->getId();
    }

    /**
     * {@inheritDoc}
     * @see Serializable::unserialize()
     */
    public function unserialize($applicationId)
    {
        $this->application = $this->getApplicationRepository()->find($applicationId);

        if (!$this->application) {
            throw new RuntimeException('Could not find application with id ' . $applicationId);
        }
    }
}
