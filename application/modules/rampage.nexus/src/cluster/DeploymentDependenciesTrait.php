<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2014 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 */

namespace rampage\nexus\cluster;

use rampage\nexus\DeployEvent;
use rampage\nexus\DeployStrategyManager;
use rampage\nexus\DeploymentConfig;
use rampage\nexus\PackageStorage;
use rampage\nexus\orm\DeploymentRepository;
use rampage\nexus\orm\DeploymentRepositoryAwareTrait;
use rampage\nexus\package\PackageTypeManager;
use rampage\nexus\entities\ApplicationInstance;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceLocatorInterface;


trait DeploymentDependenciesTrait
{
    use DeploymentRepositoryAwareTrait;

    /**
     * @var DeployStrategyManager
     */
    protected $deployStrategyManager;

    /**
     * @var PackageStorage
     */
    protected $packageStorage;

    /**
     * @var PackageTypeManager
     */
    protected $packageTypeManager;

    /**
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var DeployEvent
     */
    private $event;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return self
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        if ($this->event) {
            $this->event->setServiceLocator($serviceManager);
        }

        $this->setDependenciesFromServiceLocator($serviceManager);
        return $this;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return \rampage\nexus\cluster\DeploymentDependenciesTrait
     */
    protected function setDependenciesFromServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->setDeployStrategyManager($serviceLocator->get(DeployStrategyManager::class));
        $this->setPackageStorage($serviceLocator->get(PackageStorage::class));
        $this->setDeploymentRepository($serviceLocator->get(DeploymentRepository::class));
        $this->setPackageTypeManager($serviceLocator->get(PackageTypeManager::class));

        return $this;
    }

    /**
     * @param DeployStrategyManager $manager
     * @return self
     */
    public function setDeployStrategyManager(DeployStrategyManager $manager)
    {
        $this->deployStrategyManager = $manager;
        return $this;
    }

    /**
     * @param \rampage\nexus\PackageStorage $packageStoreage
     * @return self
     */
    public function setPackageStorage(PackageStorage $packageStorage)
    {
        if ($this->packageTypeManager) {
            $this->packageTypeManager->setPackageStorage($packageStorage);
        }

        $this->packageStorage = $packageStorage;
        return $this;
    }

    /**
     * @param \rampage\nexus\DeploymentConfig $deploymentConfig
     * @return self
     */
    public function setDeploymentConfig(DeploymentConfig $config)
    {
        $this->deploymentConfig = $config;
        return $this;
    }

    /**
     * @param \rampage\nexus\package\PackageTypeManager $packageTypeManager
     * @return self
     */
    public function setPackageTypeManager(PackageTypeManager $packageTypeManager)
    {
        if ($this->packageStorage) {
            $packageTypeManager->setPackageStorage($this->packageStorage);
        }

        $this->packageTypeManager = $packageTypeManager;
        return $this;
    }

    /**
     * @param DeployEvent $event
     * @return self
     */
    public function setEvent(DeployEvent $event)
    {
        if ($this->serviceManager) {
            $event->setServiceLocator($this->serviceManager);
        }

        $this->event = $event;
        return $this;
    }

    /**
     * @return \rampage\nexus\DeployEvent
     */
    public function injectDeployEventDependencies(DeployEvent $event, ApplicationInstance $application = null)
    {
        $event->setServiceLocator($this->serviceManager)
            ->setWebConfig($this->webConfig)
            ->setTarget($this);

        if ($application) {
            $event->setApplication($application)
                ->setPackage($this->packageTypeManager->createFromApplication($application))
                ->setDeployStrategy($this->deployStrategyManager->get($application->getDeployStrategy()));
        }

        return $event;
    }
}
