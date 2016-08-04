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

namespace Rampage\Nexus\Job;

use Rampage\Nexus\Exception\UnexpectedValueException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Zend\Stdlib\SplPriorityQueue;


/**
 * Implements a job sequence aggregation
 */
class JobAggregate implements JobInterface, ContainerAwareInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    use ContainerAwareTrait;

    /**
     * @var SplPriorityQueue|JobInterface[]
     */
    private $jobs;

    /**
     * @var int
     */
    private $priority;

    /**
     * Constructor
     */
    public function __construct($priority = 1)
    {
        $this->priority = (int)$priority;
        $this->jobs = new SplPriorityQueue();
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Jobs\QueueInterface::schedule()
     */
    public function add(JobInterface $job)
    {
        $this->jobs->insert($job, $job->getPriority());
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Job\JobInterface::getPriority()
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * {@inheritDoc}
     * @see Serializable::serialize()
     */
    public function serialize()
    {
        $queue = clone $this->jobs;
        $data = [
            'p' => $this->priority,
            'j' => []
        ];

        $queue->setExtractFlags(SplPriorityQueue::EXTR_BOTH);

        foreach ($queue as $struct) {
            list($job, $priority) = $struct;
            $data['j'][] = [get_class($job), serialize($job), $priority];
        }

        return json_encode($data);
    }

    /**
     * {@inheritDoc}
     * @see Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        $data = json_decode($serialized);

        if (!isset($data->p) || !isset($data->j) || !is_array($data->j)) {
            throw new UnexpectedValueException('Serialized job aggregation segmentation invalid');
        }

        $this->priority = $data->p;
        $this->jobs = new SplPriorityQueue();

        foreach ($data->j as $struct) {
            list($class, $jobData, $priority) = $struct;

            $job = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
            if (!$job instanceof JobInterface) {
                throw new UnexpectedValueException('Invalid job type: ' . $class);
            }

            $this->prepareJob($job);
            $job->unserialize($jobData);
            $this->jobs->insert($job, $priority);
        }
    }

    /**
     * @param JobInterface $job
     */
    protected function prepareJob(JobInterface $job)
    {
        if ($this->container && ($job instanceof ContainerAwareInterface)) {
            $job->setContainer($this->container);
        }

        if ($this->logger && ($job instanceof LoggerAwareInterface)) {
            $job->setLogger($this->logger);
        }
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Job\JobInterface::run()
     */
    public function run()
    {
        foreach ($this->jobs as $job) {
            $this->prepareJob($job);
            $job->run();
        }
    }
}

