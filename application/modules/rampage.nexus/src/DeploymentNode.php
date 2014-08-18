<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2014 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 */

namespace rampage\nexus;

use rampage\nexus\entities\ApplicationInstance;

use Zend\Http\Request as HttpRequest;
use Zend\Http\Client as HttpClient;
use Zend\Http\Client\Adapter\Curl as CurlHttpAdapter;
use Zend\Json\Json;
use Zend\Stdlib\Hydrator\HydratorInterface;

class DeploymentNode
{
    /**
     * @var DeploymentConfig
     */
    protected $config;

    /**
     * @var orm\DeploymentRepository
     */
    protected $repository;

    /**
     * @var PackageStorage
     */
    protected $packageStorage = null;

    /**
     * @var HttpClient
     */
    protected $httpClient = null;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * @var PackageInstallerManager
     */
    protected $applicationPackageManager = null;

    /**
     * @var DeployStrategyManager
     */
    protected $deployStrategyManager = null;

    /**
     * @param DeploymentConfig $config
     * @param orm\DeploymentRepository $repository
     */
    public function __construct(DeploymentConfig $config, orm\DeploymentRepository $repository, PackageStorage $packageStorage, PackageInstallerManager $packageManager, DeployStrategyManager $strategyManager)
    {
        $this->config = $config;
        $this->repository = $repository;
        $this->packageStorage = $packageStorage;
        $this->applicationPackageManager = $packageManager;
        $this->deployStrategyManager = $strategyManager;
        $this->hydrator = new hydration\ApplicationInstanceHydrator($repository);

        $this->httpClient = new HttpClient($this->config->getMasterApiUrl());
        $this->httpClient->setAdapter(new CurlHttpAdapter());
    }

    /**
     * @param entities\ApplicationInstance $application
     * @return self
     */
    protected function updateApplicationFromMaster(ApplicationInstance $application)
    {
        $request = new HttpRequest();
        $request->setUri($this->config->getMasterApiUrl('application/' . $application->getId()))
            ->setMethod(HttpRequest::METHOD_GET);

        $response = $this->httpClient->send($request);
        if (!$response->isSuccess()) {
            throw new \RuntimeException('Failed to load application info from master');
        }

        $data = Json::decode($response->getBody(), Json::TYPE_ARRAY);
        $this->hydrator->hydrate($data, $application);

        return $this;
    }

    /**
     * @param ApplicationInstance $application
     * @throws \RuntimeException
     * @return self
     */
    public function downloadArchiveFromMaster(ApplicationInstance $application)
    {
        $archive = $application->getCurrentApplicationPackageFile();

        if ($archive->exists()) {
            return $this;
        }

        $request = new HttpRequest();
        $request->setUri($this->config->getMasterApiUrl('application/package/' . $application->getId()))
            ->setMethod(HttpRequest::METHOD_GET);

        $this->packageStorage->mkdir(dirname($archive->getRelativePath()));
        $this->httpClient->setStream($archive->getPathname());

        $response = $this->httpClient->send($request);
        if (!$response->isSuccess()) {
            throw new \RuntimeException('Failed to download application package from master');
        }

        return $this;
    }

    /**
     * @param int $applicationId
     * @return \rampage\nexus\entities\ApplicationInstance
     */
    public function prepareApplicationInstance($applicationId)
    {
        if (!$this->config->isNode()) {
            return $this->repository->findApplicationById($applicationId);
        }

        $application = $this->repository->findApplicationByMasterId($applicationId);

        if (!$application) {
            $application = new ApplicationInstance();
        }

        $this->updateApplicationFromMaster($application);
        $this->repository->persist($application);
        $this->repository->flush($application);

        $application->setPackageStorage($this->packageStorage);
        $this->downloadArchiveFromMaster($application);

        return $application;
    }

    /**
     * @param ApplicationInstance|int $application
     * @return self
     */
    public function deploy($application)
    {
        if (!$application instanceof ApplicationInstance) {
            $applicationId = (int)$application;
            $application = $this->prepareApplicationInstance($applicationId);

            if (!$application) {
                throw new \RuntimeException(sprintf('Failed to prepare application %d for deployment', $applicationId));
            }
        }

        $application->setDeployStrategyManager($this->deployStrategyManager);
        $application->setPackageStorage($this->packageStorage);

        $installer = $this->applicationPackageManager->createInstallerForApplication($application);
        $installer->install($application);

        $this->repository->flush($application);
        return $application;
    }

    /**
     * @param ApplicationInstance $application
     */
    public function remove(ApplicationInstance $application)
    {
        $application->setDeployStrategyManager($this->deployStrategyManager);
        $application->setPackageStorage($this->packageStorage);

        $installer = $this->applicationPackageManager->createInstallerForApplication($application);
        $installer->remove($application);

        $this->repository->getEntityManager()->remove($application);
    }
}
