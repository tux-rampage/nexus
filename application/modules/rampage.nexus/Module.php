<?php
namespace rampage\nexus;

use rampage\core\AbstractModule;
use rampage\core\ModuleManifest;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface
{
    public function __construct()
    {
        parent::__construct(new ModuleManifest(__DIR__));
    }

    public function getConfig()
    {
        return $this->fetchConfigArray();
    }


}
