<?php
/**
 * Copyright (c) 2016 Axel Helmert
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Axel Helmert
 * @copyright Copyright (c) 2016 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
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
