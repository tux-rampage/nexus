<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2014 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 */

namespace rampage\nexus;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\ProvidesEvents;

use Zend\Stdlib\SplPriorityQueue;

use SplFileInfo;


/**
 * Application package manager to retrieve the installer implementation for a package file
 */
class ApplicationPackageManager implements EventManagerAwareInterface
{
    use ProvidesEvents;

    /**
     * @var ApplicationPackageInterface[]
     */
    protected $packageTypes;

    /**
     * @var string[]
     */
    protected $eventIdentifier = array(
        'ApplicationPackageManager'
    );

    /**
     * Construct
     */
    public function __construct()
    {
        $this->packageTypes = new SplPriorityQueue();

        $this->addPackageType(new ComposerApplicationPackage());
    }

    /**
     * @param ApplicationPackageInterface $type
     */
    public function addPackageType(ApplicationPackageInterface $type, $priority = 10)
    {
        if ($type instanceof EventManagerAwareInterface) {
            $type->setEventManager($this->getEventManager());
        }

        $this->packageTypes->insert($type, $priority);
        return $this;
    }

    /**
     * @param SplFileInfo $archive
     * @return ApplicationPackageInterface
     */
    public function getPackageInstaller(SplFileInfo $archive)
    {
        $queue = clone $this->packageTypes;

        foreach ($queue as $type) {
            if (!$type->supports($archive)) {
                continue;
            }

            $instance = clone $type;
            $instance->load($archive);

            return $instance;
        }

        throw new \DomainException(sprintf('Unable to find a supported package type for "%s"', $archive->getFilename()));
    }

    /**
     * @param \rampage\nexus\entities\ApplicationInstance $application
     * @return ApplicationPackageInterface
     */
    public function createInstallerForApplication(entities\ApplicationInstance $application)
    {
        /* @var $application \rampage\nexus\entities\ApplicationInstance */
        $archive = $application->getCurrentApplicationPackageFile();
        if (!$archive) {
            throw new \RuntimeException(sprintf('Could not find application package for application "%s" (%d)', $application->getName(), $application->getId()));
        }

        $file = new SplFileInfo($archive->getPathname());
        $installer = $this->getPackageInstaller($file);

        $installer->load($file);
        return $installer;
    }
}
