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

namespace Rampage\Nexus\BuildSystem\Jenkins\PackageScanner;

use Rampage\Nexus\BuildSystem\Jenkins\BuildNotification;
use Rampage\Nexus\BuildSystem\Jenkins\Repository\StateRepositoryInterface;
use Rampage\Nexus\BuildSystem\Jenkins\Job;
use Rampage\Nexus\BuildSystem\Jenkins\ClientInterface;
use Rampage\Nexus\BuildSystem\Jenkins\Build;
use Rampage\Nexus\BuildSystem\Jenkins\Artifact;
use Rampage\Nexus\BuildSystem\Jenkins\ClientFactoryInterface;

use Rampage\Nexus\Package\ZpkPackage;
use Rampage\Nexus\Package\ComposerPackage;
use Rampage\Nexus\Package\PackageInterface;

use Rampage\Nexus\Archive\ArchiveLoaderInterface;
use Rampage\Nexus\NoopLogger;
use Rampage\Nexus\Repository\PackageRepositoryInterface;
use Rampage\Nexus\Entities\ApplicationPackage;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Rampage\Nexus\FileSystemInterface;


/**
 * Implements the package scanner
 */
class PackageScanner implements PackageScannerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var PackageRepositoryInterface
     */
    private $repository;

    /**
     * @var StateRepositoryInterface
     */
    private $stateRepository;

    /**
     * @var ClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @var ArchiveLoaderInterface
     */
    private $archiveLoader;

    /**
     * @var FileSystemInterface
     */
    private $filesystem;

    /**
     * @param StateRepositoryInterface $stateRepository
     * @param ClientInterface $api
     */
    public function __construct(
        PackageRepositoryInterface $repository,
        StateRepositoryInterface $stateRepository,
        ClientFactoryInterface $clientFactory,
        ArchiveLoaderInterface $archiveLoader,
        FileSystemInterface $filesystem)
    {
        $this->repository = $repository;
        $this->stateRepository = $stateRepository;
        $this->clientFactory = $clientFactory;
        $this->archiveLoader = $archiveLoader;
        $this->filesystem = $filesystem;
        $this->logger = new NoopLogger();
    }

    /**
     * @param string $name
     * @param string[] $patters
     * @return boolean
     */
    private function jobNameMatches($name, $patters)
    {
        foreach ($patters as $item) {
            $item = $this->normalizeJobPattern($item);
            if (($item == $name) || (strpos($name, $item . '/') === 0)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if job is accepted by the given instance config
     *
     * @param InstanceConfig $instance
     * @param Job|string $job
     */
    private function isJobAccepted(InstanceConfig $instance, $job)
    {
        $name = ($job instanceof Job)? $job->getFullName() : (string)$job;

        if ($this->jobNameMatches($name, $instance->getExcludeProjects())) {
            return false;
        }

        $includes = $instance->getIncludeProjects();
        $accepted = !count($includes);

        if ($this->jobNameMatches($name, $includes)) {
            return true;
        }

        return $accepted;
    }

    /**
     * @param unknown $name
     * @return mixed
     */
    private function normalizeJobPattern($name)
    {
        return str_replace('/', '/job/', $name);
    }

    /**
     * @param Build $build
     * @return Artifact[]
     */
    private function getFilteredArtifacts(Build $build)
    {
        $filter = function(Artifact $artifact) {
            $suffix = substr($artifact->getFileName(), -4);
            return in_array($suffix, ['.zip', '.zpk']);
        };

        return new \CallbackFilterIterator(new \ArrayIterator($build->getArtifacts()), $filter);
    }

    /**
     * @param Artifact $artifact
     * @param InstanceConfig $instance
     * @return string
     */
    private function buildLocalFilename(Artifact $artifact, InstanceConfig $instance)
    {
        return sprintf(
            '%s.%d.%s-$%s',
            $instance->getId(),
            $artifact->getBuild()->getId(),
            md5($artifact->getBuild()->getJob()->getFullName()),
            $artifact->getFileName()
        );
    }

    /**
     * Returns the Package information for this build
     *
     * @param Build $build
     * @param Artifact $artifact
     * @return ZpkPackage|ComposerPackage
     */
    private function getPackage(InstanceConfig $instance, Artifact $artifact)
    {
        $zpk = $artifact->getFileName() . '.xml';
        $composer = $artifact->getFileName() . '.json';
        $build = $artifact->getBuild();

        if (null !== ($zpkDesc = $build->getArtifact($zpk))) {
            return new ZpkPackage($zpkDesc->getContents());
        }

        if (null !== ($composerDesc = $build->getArtifact($composer))) {
            return new ComposerPackage($composerDesc->getContents());
        }

        if (!$instance->isArtifactScanEnabled()) {
            return null;
        }

        // Try pulling the archive
        $filename = $this->downloadDirectory . '/' . $this->buildLocalFilename($artifact, $instance);
        $artifact->download($filename);

        try {
            $archive = new \PharData($filename);
            $package = $this->archiveLoader->getPackage($archive);

            return $package;
        } catch (\Throwable $e) {
            $this->logger->info(' - Unusable archive: ' . $artifact->getRelativePath());
            $this->filesystem->delete($filename);
        }

        return null;
    }

    /**
     * @param PackageInterface $package
     * @param bool $isStable
     */
    private function processPackage(PackageInterface $package, $isStable)
    {
        $existing = $this->repository->findOne($package->getId());
        if ($existing) {
            return;
        }

        $entity = new ApplicationPackage($package);

        $entity->setIsStable($isStable);
        $this->repository->save($entity);
    }

    /**
     * @param ClientInterface $client
     * @param Build $build
     */
    private function scanArtifacts(ClientInterface $client, Build $build, InstanceConfig $instance)
    {
        $instanceId = $instance->getId();
        $job = $build->getJob();

        if (!$build->isUsable()) {
            $this->logger->debug(sprintf('Unusable build %d in job "%s" (%s).', $build->getId(), $job->getFullName(), $build->getResult()));
            return;
        }

        $this->logger->debug(sprintf('Scanning build %d in job "%s" ...', $build->getId(), $job->getFullName()));
        $artifacts = $this->getFilteredArtifacts($build);

        foreach ($artifacts as $artifact) {
            $package = $this->getPackage($instance, $artifact);
            if (!$package) {
                $this->logger->debug(sprintf('Skip artifact "%s" of build %d in job "%s" - no package descriptor', $artifact, $build->getId(), $job->getFullName()));
                continue;
            }

            $package->setBuildId($instanceId . '.' . $build->getId());
            $package->setArchive(sprintf(
                'jenkins://%s/%s/build/%d/%s',
                $instanceId,
                $build->getJob()->getFullName(),
                $build->getId(),
                $artifact->getRelativePath()
            ));

            $this->logger->debug(sprintf('Importing artifact "%s" of build %d in job "%s" to package "%s" ...', $artifact, $build->getId(), $job->getFullName(), $package->getId()));
            $this->processPackage($package, $build->isStable());
        }
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\BuildSystem\Jenkins\PackageScanner\PackageScannerInterface::notify()
     */
    public function notify(InstanceConfig $instance, BuildNotification $notification)
    {
        $facetNames = $notification->getFacetedJobNames();
        $jobName = $notification->getJobName();

        if (!empty($facetNames)) {
            $jobName .= '/job/' . implode('/job/', $facetNames);
        }

        if (!$this->isJobAccepted($instance, $jobName)) {
            return;
        }

        $client = $this->clientFactory->createJenkinsClient($instance->getJenkinsUrl());
        $job = $client->getJob($notification->getJobName());

        while (!empty($facetNames)) {
            $name = array_shift($facetNames);
            $job = $client->getJob($name, $job);
        }

        $processed = $this->stateRepository->getProcessedBuilds($instance, $job);
        if (in_array((string)$notification->getBuildId(), $processed)) {
            return;
        }

        $build = $job->getBuild($notification->getBuildId());

        $this->scanArtifacts($client, $build, $instance);
        $this->stateRepository->addProcessedBuild($instance, $build);
    }

    /**
     * Scan jobs
     *
     * @param ClientInterface $client
     * @param InstanceConfig $instance
     * @param string[] $jobs
     * @param Job $group
     */
    private function scanJobs(ClientInterface $client, InstanceConfig $instance, $jobs, Job $group = null)
    {
        foreach ($jobs as $name) {
            $job = $client->getJob($name, $group);

            if (!$this->isJobAccepted($instance, $job)) {
                $this->logger->debug(sprintf('Skipping excluded job "%s"', $job->getFullName()));
                continue;
            }

            if ($job->isGroup()) {
                $this->logger->debug(sprintf('Scanning group job "%s" ...', $job->getFullName()));
                $this->scanJobs($client, $instance, $job->getJobs(), $job);
                continue;
            }

            $processed = $this->stateRepository->getProcessedBuilds($instance, $job);

            foreach ($job->getBuilds() as $buildId) {
                if (in_array((string)$buildId, $processed)) {
                    $this->logger->debug(sprintf('Skip already processed build %d in job "%s".', $buildId, $job->getFullName()));
                    continue;
                }

                try {
                    $build = $job->getBuild($buildId);

                    if (!$build->isPending()) {
                        $this->logger->debug(sprintf('Skip pending build %d in job "%s".', $buildId, $job->getFullName()));
                        continue;
                    }

                    $this->scanArtifacts($client, $build, $instance);
                    $this->stateRepository->addProcessedBuild($instance, $build);
                } catch (\Throwable $e) {
                    $this->logger->error($e->__toString());
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\BuildSystem\Jenkins\PackageScanner\PackageScannerInterface::scan()
     */
    public function scan(InstanceConfig $instance)
    {
        $this->logger->info(sprintf('Scanning jenkins instance "%s" for packages ...', $instance->getId()));

        $client = $this->clientFactory->createJenkinsClient($instance->getJenkinsUrl());
        $this->scanJobs($client, $instance, $client->getJobs());
    }
}
