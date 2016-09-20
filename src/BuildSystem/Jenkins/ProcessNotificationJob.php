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

namespace Rampage\Nexus\BuildSystem\Jenkins;

use Rampage\Nexus\Job\JobInterface;
use Rampage\Nexus\Job\ContainerAwareInterface;
use Rampage\Nexus\Job\ContainerAwareTrait;

use Rampage\Nexus\BuildSystem\Jenkins\Repository\InstanceRepositoryInterface;
use Rampage\Nexus\BuildSystem\Jenkins\PackageScanner\PackageScannerInterface;
use Rampage\Nexus\Exception\UnexpectedValueException;
use Rampage\Nexus\Exception\LogicException;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Rampage\Nexus\NoopLogger;


/**
 * Implements the job to process notifications
 */
class ProcessNotificationJob implements JobInterface, ContainerAwareInterface, LoggerAwareInterface
{
    use ContainerAwareTrait;
    use LoggerAwareTrait;

    /**
     * @var BuildNotification
     */
    private $notification;

    /**
     * @var string
     */
    private $instanceId;

    /**
     * @param BuildNotification $notification
     * @param unknown $instanceId
     */
    public function __construct(BuildNotification $notification, $instanceId = null)
    {
        $this->notification = $notification;
        $this->instanceId = null;
        $this->logger = new NoopLogger();
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Job\JobInterface::getPriority()
     */
    public function getPriority()
    {
        return 1;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Job\JobInterface::run()
     */
    public function run()
    {
        if (!$this->container) {
            throw new LogicException('Cannot execute this job without an IoC container');
        }

        /* @var $repository InstanceRepositoryInterface */
        /* @var $scanner PackageScannerInterface */
        $repository = $this->container->get(InstanceRepositoryInterface::class);
        $scanner = $this->container->get(PackageScannerInterface::class);
        $instances = null;

        if ($this->instanceId) {
            $instance = $repository->find($this->instanceId);

            if ($instance) {
                $instances = [ $instance ];
            } else {
                $this->logger->warning(sprintf(
                    '%s: Request explicit instance "%s", which does not exist. Trying to find instances.',
                    __METHOD__, $this->instanceId
                ));
            }
        }

        if (!$instances) {
            $instances = $repository->findByBuildNotification($this->notification);
        }

        /* @var $instance PackageScanner\InstanceConfig */
        foreach ($instances as $instance) {
            try {
                $this->logger->debug(sprintf(
                    '%s: Trigger build notification %s#%d on instance "%s"',
                    __METHOD__,
                    $this->notification->getJobName(),
                    $this->notification->getBuildId(),
                    $instance->getId()
                ));

                $scanner->notify($instance, $this->notification);
            } catch (\Exception $e) {
                $this->logger->error(__METHOD__ . ': ' . $e->__toString());
            }
        }
    }

    /**
     * {@inheritDoc}
     * @see Serializable::serialize()
     */
    public function serialize()
    {
        return json_encode([
            'n' => $this->notification->toArray(),
            'i' => $this->instanceId
        ]);
    }

    /**
     * {@inheritDoc}
     * @see Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        $data = json_decode($serialized, true);
        if (!isset($data['n']) || !is_array($data['n'])) {
            throw new UnexpectedValueException('Missing notification data from serialized value');
        }

        $this->notification = new BuildNotification($data['n']);
        $this->instanceId = isset($data['i'])? $data['i'] : null;
    }
}
