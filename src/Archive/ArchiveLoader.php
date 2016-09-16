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

namespace Rampage\Nexus\Archive;

use Rampage\Nexus\FileSystemInterface;
use Rampage\Nexus\FileSystem;
use Rampage\Nexus\Exception\RuntimeException;

use PharData;
use Rampage\Nexus\Archive\PackageLoader\PackageLoaderInterface;


/**
 * Implements the archive loader
 */
class ArchiveLoader implements ArchiveLoaderInterface
{
    /**
     * @var DownloaderInterface[]
     */
    protected $downloaders = [];

    /**
     * @var PackageLoaderInterface[]
     */
    protected $packageLoaders = [];

    /**
     * @var string
     */
    protected $downloadDirectory;

    /**
     * @var FileSystemInterface
     */
    protected $filesystem;

    /**
     * @param   string              $dir        The download directory for archives
     * @param   FileSystemInterface $filesystem The filesystem API instance
     */
    public function __construct($dir, FileSystemInterface $filesystem = null)
    {
        $this->downloadDirectory = $dir;
        $this->filesystem = $filesystem? : new FileSystem();

        $this->addPackageLoader(new PackageLoader\ZpkLoader())
            ->addPackageLoader(new PackageLoader\ComposerLoader());

        $this->addDownloader(new HttpDownloader());
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Archive\ArchiveLoaderInterface::getDownloadDirectory()
     */
    public function getDownloadDirectory()
    {
        return $this->downloadDirectory;
    }

    /**
     * Add a downloader
     *
     * @param   DownloaderInterface $downloader
     * @return  self
     */
    public function addDownloader(DownloaderInterface $downloader)
    {
        $this->downloaders[] = $downloader;
        return $this;
    }

    /**
     * Adds a package loader
     *
     * @param   PackageLoaderInterface  $loader A apackage loader implementation to utilize
     * @return  self
     */
    public function addPackageLoader(PackageLoaderInterface $loader)
    {
        $this->packageLoaders[] = $loader;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Archive\ArchiveLoaderInterface::ensureLocalArchiveFile()
     */
    public function ensureLocalArchiveFile($archive)
    {
        if (is_file($archive)) {
            return $archive;
        }

        foreach ($this->downloaders as $downloader) {
            if (!$downloader->canDownload($archive)) {
                continue;
            }

            $this->filesystem->ensureDirectory($this->downloadDirectory);
            $file = $this->downloadDirectory . '/' . $downloader->getFilenameFromUrl($archive);

            if ($downloader->download($archive, $file)) {
                return $file;
            }
        }

        throw new RuntimeException('Could not donwload url to a local file: ' . $archive);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Archive\ArchiveLoaderInterface::getPackage()
     */
    public function getPackage(PharData $archive)
    {
        foreach ($this->packageLoaders as $loader) {
            if ($loader->canReadFromArchive($archive)) {
                return $loader->load($archive);
            }
        }

        throw new RuntimeException('Unable to read a Package from the given archive: ' . $archive->getPathname());
    }
}
