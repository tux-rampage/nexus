<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2014 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 */

namespace rampage\nexus\package;

use SplFileInfo;

use rampage\nexus\entities\ApplicationInstance;
use rampage\nexus\PackageStorage;
use rampage\nexus\entities\ApplicationVersion;

use Zend\Stdlib\SplPriorityQueue;


/**
 * Application package manager to retrieve the installer implementation for a package file
 */
class PackageTypeManager
{
    /**
     * @var ApplicationPackageInterface[]
     */
    protected $packageTypes;

    /**
     * @var PackageStorage
     */
    protected $packageStorage;

    /**
     * Construct
     */
    public function __construct(PackageStorage $packageStorage)
    {
        $this->packageStorage = $packageStorage;
        $this->packageTypes = new SplPriorityQueue();

        $this->addPackageType(new ComposerPackage());
    }

    /**
     * @param ApplicationPackageInterface $type
     */
    public function addPackageType(ApplicationPackageInterface $type, $priority = 10)
    {
        $this->packageTypes->insert($type, $priority);
        return $this;
    }

    /**
     * @param SplFileInfo $archive
     * @return ApplicationPackageInterface
     */
    public function createFromArchive(SplFileInfo $archive)
    {
        $queue = clone $this->packageTypes;

        foreach ($queue as $type) {
            if (!$type->supports($archive)) {
                continue;
            }

            return $type->create($archive);
        }

        throw new \DomainException(sprintf('Unable to find a supported package type for "%s"', $archive->getFilename()));
    }

    /**
     * @param \rampage\nexus\entities\ApplicationInstance $application
     * @return ApplicationPackageInterface
     */
    public function createFromApplication(ApplicationInstance $application)
    {
        $version = $application->getCurrentVersion();

        if (!$version) {
            throw new \RuntimeException(sprintf('No current version for application "%s" (%d) to instanciate a package for.', $application->getName(), $application->getId()));
        }

        return $this->createFromApplicationVersion($version);
    }

    /**
     * @param ApplicationVersion $version
     * @return ApplicationPackageInterface
     */
    public function createFromApplicationVersion(ApplicationVersion $version)
    {
        $archive = $this->packageStorage->getPackageFile($version);

        if (!$archive) {
            $application = $version->getApplication();
            throw new \RuntimeException(sprintf(
                'Could not find application package for application "%s" (%d) version "%s".',
                $application->getName(),
                $application->getId(),
                $version->getVersion()
            ));
        }

        $file = new SplFileInfo($archive->getPathname());
        return $this->createFromArchive($file);
    }
}
