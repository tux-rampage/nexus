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

use Rampage\Nexus\Exception\RuntimeException;

use SplFileInfo;
use PharData;

/**
 * Interface for loading archive files
 */
interface ArchiveLoaderInterface
{
    /**
     * Returns the download directory
     *
     * @return string
     */
    public function getDownloadDirectory();

    /**
     * Adds a downloader implementation
     *
     * @param DownloaderInterface $downloader
     */
    public function addDownloader(DownloaderInterface $downloader);

    /**
     * Ensures the archive is available locally
     *
     * @param   string      $archive    The path or URI to the archive
     * @return  SplFileInfo             The file info instance for the local archive file
     * @throws  RuntimeException        When the file cannot be provided locally
     */
    public function ensureLocalArchiveFile($archive);

    /**
     * Returns the package from the given archive
     *
     * @param   PharData            $archive    The archive instance
     * @return  PackageInterface                The resulting package
     * @throws  RuntimeException                When the packagetype is not available
     */
    public function getPackage(PharData $archive);
}
