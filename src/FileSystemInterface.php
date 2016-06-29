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

namespace Rampage\Nexus;


/**
 * Filesystem API
 */
interface FileSystemInterface
{
    /**
     * Ensure existence of a directory
     *
     * @param   string  $dir
     * @param   int     $mode
     * @throws  Exception\RuntimeException
     * @return  self
     */
    public function ensureDirectory($dir, $mode = null);

    /**
     * Remove a file or a whole directory.
     *
     * Directories will be deleted recursively. This method will not follow symlinks
     *
     * @param   string  $fileOrDirectory
     * @return  self
     */
    public function delete($fileOrDirectory);

    /**
     * Remove the contents of the given directory
     *
     * This will remove all files in the current directory, but not the directory itself
     *
     * @param   string  $dir    The directory to purge
     * @return  self
     *
     * @throws  Exception\RuntimeException  When the directory does not exists or the given path is not a directory
     */
    public function purgeDirectory($dir);
}
