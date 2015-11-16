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
        return $this;
    }

    /**
     * @param PackageInterface $package
     * @return InstallerInterface|null
     */
    public function getInstaller(PackageInterface $package)
    {
        foreach (clone $this->packageTypes as $installer) {
            if ($installer->supports($package)) {
                return $installer;
            }
        }

        return null;
    }
}
