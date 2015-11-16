<?php
/**
 * Copyright (c) 2015 Axel Helmert
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
 * @copyright Copyright (c) 2015 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */
namespace rampage\nexus\node\installer;

use rampage\nexus\exceptions;
use rampage\nexus\entities\ApplicationInstance;
use rampage\nexus\package\ZpkPackage;

use PharData;
use SimpleXMLElement;
use SplFileInfo;


class ZpkInstaller extends AbstractInstaller
{
    /**
     * @var ZpkPackage
     */
    protected $package;

    /**
     * @var string
     */
    protected $extractedScriptsPath = null;

    /**
     * {@inheritdoc}
     * @return ZpkPackage
     */
    public function getPackage()
    {
        if ($this->package === null) {
            $this->assertArchive();

            if (!isset($this->archive['deployment.xml'])) {
                throw new exceptions\RuntimeException('Could not find deployment.xml');
            }

            $xml = new SimpleXMLElement($this->archive['deployment.xml']->getContents());
            $this->package = new ZpkPackage($xml);
        }

        return $this->package;
    }

    /**
     * @see \rampage\nexus\package\InstallerInterface::getTypeName()
     */
    public function getTypeName()
    {
        return ZpkPackage::TYPE_ZPK;
    }

    /**
     * @see \rampage\nexus\package\InstallerInterface::getWebRoot()
     */
    public function getWebRoot(ApplicationInstance $application)
    {
        $package = $this->getPackage();
        $docRoot = trim($package->getDocumentRoot(), '/');
        $appDir = trim($package->getAppDir(), '/');

        if (strpos($docRoot, $appDir . '/') === 0) {
            $docRoot = substr($docRoot, strlen($appDir) + 1);
        }

        return $docRoot;
    }

    protected function runHookScript(ApplicationInstance $application, $name)
    {
        // TODO: Implement hooks
    }

    /**
     * @see \rampage\nexus\package\InstallerInterface::install()
     */
    public function install(ApplicationInstance $application)
    {
        $package = $this->getPackage();
        $appDir = trim($package->getAppDir(), '/');
        $prefix = 'phar://' . $this->archiveInfo->getPathname() . '/' . $appDir . '/';
        $iterator = new \RecursiveIteratorIterator($this->archive, \RecursiveIteratorIterator::SELF_FIRST);

        // TODO: extract

        /* @var $file \PharFileInfo */
        foreach ($iterator as $filename => $file) {
            if (strpos($filename, $prefix) !== 0) {
                continue;
            }

            $target = $this->targetDirectory->getPathname() . '/'
                    . substr($filename, strlen($prefix));

            if ($file->isDir()) {
                mkdir($target, 0755, true);
                continue;
            }

            file_put_contents($target, $file->getContent());
        }
    }

    /**
     * @see \rampage\nexus\package\InstallerInterface::remove()
     */
    public function remove(ApplicationInstance $application)
    {
        // TODO Auto-generated method stub
    }

    /**
     * @see \rampage\nexus\package\InstallerInterface::rollback()
     */
    public function rollback(ApplicationInstance $application)
    {
        // TODO Auto-generated method stub
    }
}