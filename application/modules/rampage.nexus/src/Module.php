<?php
namespace rampage\nexus;

use rampage\core\AbstractModule;
use rampage\core\ModuleManifest;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

/**
 * Module entry
 */
class Module extends AbstractModule implements ConfigProviderInterface
{
    /**
     * Module version
     */
    const VERSION = '1.0.0';

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
     * {@inheritdoc}
     * @see \Zend\ModuleManager\Feature\ConfigProviderInterface::getConfig()
     */
    public function getConfig()
    {
        return $this->fetchConfigArray();
    }
}
