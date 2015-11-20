<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2014 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 */

namespace rampage\nexus\node\installer;

use Zend\Stdlib\SplPriorityQueue;
use rampage\nexus\PackageInterface;


/**
 * Application package manager to retrieve the installer implementation for a package file
 */
class InstallerManager
{
    /**
     * @var InstallerInterface[]
     */
    protected $packageTypes;

    /**
     * Installer by type cache
     *
     * @var InstallerInterface[]
     */
    protected $cache = [];

    /**
     * @param PackageStorage $packageStorage
     */
    public function __construct()
    {
        $this->packageTypes = new SplPriorityQueue();
        $this->addPackageType(new ComposerInstaller());
    }

    /**
     * @param InstallerInterface $type
     */
    public function addPackageType(InstallerInterface $type, $priority = 10)
    {
        $this->packageTypes->insert($type, $priority);
        $this->cache = [];

        return $this;
    }

    /**
     * @param PackageInterface $package
     * @return InstallerInterface|null
     */
    public function getInstaller(PackageInterface $package)
    {
        $type = $package->getType();

        if (isset($this->cache[$type])) {
            return $this->cache[$type];
        }

        foreach (clone $this->packageTypes as $installer) {
            if ($installer->supports($package)) {
                $this->cache[$type] = $installer;
                return $installer;
            }
        }

        return null;
    }
}
