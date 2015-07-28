<?php
namespace rampage\nexus;

use Zend\Config\Factory as ConfigFactory;

use Zend\ModuleManager\ModuleManager;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\InitProviderInterface;

use rampage\core\modules\EventListenerProviderInterface;

/**
 * Module entry
 */
class Module implements ConfigProviderInterface,
    ServiceProviderInterface,
    InitProviderInterface,
    EventListenerProviderInterface
{
    /**
     * Module version
     */
    const VERSION = '1.0.0';

    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager = null;

    /**
     * @param string $type
     * @return array
     */
    protected function loadConfig($type)
    {
        return include __DIR__ . '/../config/' . $type . '.conf.php';
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * @param ModuleManager $manager
     */
    public function init(ModuleManagerInterface $manager)
    {
        ConfigFactory::registerReader('conf', 'ini');
        ConfigFactory::registerWriter('conf', 'ini');

        $this->serviceManager = $manager->getEvent()->getParam('ServiceManager');
//         $serviceListener = $this->serviceManager->get('ServiceListener');

        $manager->getEventManager()->attach(new ConfigListenerOptions());
//         $manager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, array($this, 'addModulePackeTypes'));

//         // Add service listeners
//         if ($serviceListener instanceof ServiceListener) {
//             $serviceListener->addServiceManager(
//                 DeployStrategyManager::class,
//                 'deploy_strategies',
//                 features\DeployStrategyProviderInterface::class,
//                 'getDeployStrategiesConfig'
//             );

//             $serviceListener->addServiceManager(
//                 WebConfigManager::class,
//                 'web_configs',
//                 features\WebConfigProviderInterface::class,
//                 'getWebConfigsConfig'
//             );

//             $serviceListener->addServiceManager(
//                 api\ServerApiManager::class,
//                 'server_apis',
//                 features\ServerApiProviderInterface::class,
//                 'getServerApisConfig'
//             );
//         }
    }

    /**
     * {@inheritdoc}
     * @see \Zend\ModuleManager\Feature\ConfigProviderInterface::getConfig()
     */
    public function getConfig()
    {
        return $this->loadConfig('module');;
    }

    /**
     * @see \Zend\ModuleManager\Feature\ServiceProviderInterface::getServiceConfig()
     */
    public function getServiceConfig()
    {
        return $this->loadConfig('services');
    }

    /**
     * @see \rampage\core\modules\EventListenerProviderInterface::getEventListeners()
     */
    public function getEventListeners()
    {
        return $this->loadConfig('events');
    }
}
