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

use Rampage\Nexus\NoopLogger;

use Psr\Log\LoggerAwareTrait;

use SplPriorityQueue;
use SplObjectStorage;
use Throwable;
use Interop\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;


/**
 * Implements a job queue that is processed after sending the resonse
 */
class PostResponseQueue implements QueueInterface
{
    use LoggerAwareTrait;

    /**
     * @var SplObjectStorage|JobInterface[]
     */
    private $jobs;

    /**
     * IoC container
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor
     */
    public function __construct(ContainerInterface $container)
    {
        $this->logger = new NoopLogger();
        $this->jobs = new SplObjectStorage();
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Jobs\QueueInterface::schedule()
     */
    public function schedule(JobInterface $job)
    {
        $this->jobs->attach($job);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Jobs\QueueInterface::cancel()
     */
    public function cancel(JobInterface $job)
    {
        $this->jobs->detach($job);
    }

    /**
     * @param JobInterface $job
     */
    private function prepareJob(JobInterface $job)
    {
        if ($job instanceof ContainerAwareInterface) {
            $job->setContainer($this->container);
        }

        if ($job instanceof LoggerAwareInterface) {
            $job->setLogger($this->logger);
        }
    }

    /**
     * Moves all jobs to a priority queue
     *
     * @return SplPriorityQueue
     */
    private function toQueue()
    {
        $queue = new SplPriorityQueue();

        foreach ($this->jobs as $job) {
            $queue->insert($job, $job->getPriority());
        }

        $this->jobs->removeAllExcept(new SplObjectStorage());
        return $queue;
    }

    /**
     * Process items
     */
    public function process()
    {
        $this->logger->debug('Running post response jobs ...');

        foreach ($this->toQueue() as $job) {
            try {
                $this->prepareJob($job);
                $job->run();
            } catch (Throwable $e) {
                $this->logger->error($e->getMessage(), ['exception' => $e]);
            }
        }

        $this->logger->debug('Completed all post response jobs.');
    }

    /**
     * Makes the implementation invokable to be attached to an emitter
     */
    public function __invoke()
    {
        $this->process();
    }
}
