<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2014 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 */

namespace Rampage\Nexus\Package\Installer;

use Rampage\Nexus\Package\ComposerPackage;
use Rampage\Nexus\Package\ZpkPackage;
use Rampage\Nexus\Package\PackageInterface;

use Rampage\Nexus\Exception\RuntimeException;
use Rampage\Nexus\Exception\LogicException;

use Interop\Container\ContainerInterface;


/**
 * Application package manager to retrieve the installer implementation for a package file
 */
class InstallerManager implements InstallerProviderInterface
{
    /**
     * @var string[]
     */
    protected $packageTypes = [];

    /**
     * Installer by type cache
     *
     * @var InstallerInterface[]
     */
    protected $prototypes = [];

    /**
     * IoC container
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param PackageStorage $packageStorage
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->packageTypes = [
            ComposerPackage::TYPE_COMPOSER => ComposerInstaller::class,
            ZpkPackage::TYPE_ZPK => ZpkInstaller::class,
        ];
    }

    /**
     * @param InstallerInterface $type
     */
    public function addPackageType($type, $class)
    {
        $this->packageTypes[$type] = $class;
        return $this;
    }

    /**
     * Creates the installer prototype
     *
     * @param   string              $type   The package type
     * @return  InstallerInterface
     * @throws  RuntimeException
     */
    protected function createInstallerPrototype($type)
    {
        if (!isset($this->packageTypes[$type])) {
            throw new RuntimeException('Unsupported package type: ' . $type);
        }

        $installer = $this->container->get($this->packageTypes[$type]);

        if (!$installer instanceof InstallerInterface) {
            $type = is_object($installer)? get_class($installer) : gettype($installer);
            throw new LogicException('Invalid installer implementation: ' . $type);
        }

        $this->prototypes[$type] = $installer;
    }

    /**
     * @param PackageInterface $package
     * @return InstallerInterface
     */
    public function getInstaller(PackageInterface $package)
    {
        $type = $package->getType();

        if (!isset($this->prototypes[$type])) {
            $this->createInstallerPrototype($type);
        }

        return clone $this->prototypes[$type];
    }
}
