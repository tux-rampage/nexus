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

/**
 * Interface for downloaders
 */
interface DownloaderInterface
{
    /**
     * Check if the downloadeer can handle the given url
     *
     * @param   string  $url    The url to download
     * @return  bool
     */
    public function canDownload($url);

    /**
     * Returns the base filename from the given URL
     *
     * @param   string  $url    The download url
     * @return  string          The resulting filename without any path information
     */
    public function getFilenameFromUrl($url);

    /**
     * Download the given URL
     *
     * @param   string  $url        The URL to download
     * @param   string  $targetFile The file to download to
     * @return  bool
     */
    public function download($url, $targetFile);
}