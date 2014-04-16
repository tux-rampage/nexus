<?php
namespace rampage\nexus;

use rampage\core\AbstractModule;
use rampage\core\ModuleManifest;

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
class Module extends AbstractModule implements ConfigProviderInterface,
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
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(new ModuleManifest(dirname(__DIR__)));
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
    }

    /**
     * {@inheritdoc}
     * @see \Zend\ModuleManager\Feature\ConfigProviderInterface::getConfig()
     */
    public function getConfig()
    {
        $config = $this->fetchConfigArray();
        $config['navigation'] = include __DIR__ . '/../conf/navigation.config.php';

        return $config;
    }

    /**
     * @see \Zend\ModuleManager\Feature\ServiceProviderInterface::getServiceConfig()
     */
    public function getServiceConfig()
    {
        return include __DIR__ . '/../conf/services.config.php';
    }

    /**
     * @see \rampage\core\modules\EventListenerProviderInterface::getEventListeners()
     */
    public function getEventListeners()
    {
        return include __DIR__ . '/../conf/events.config.php';
    }
}
