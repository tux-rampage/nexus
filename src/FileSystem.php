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

namespace Rampage\Nexus;

use RecursiveDirectoryIterator;
use DirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Filesystem API
 */
class FileSystem implements FileSystemInterface
{
    /**
     * Ensure existence of a directory
     *
     * @param string $dir
     * @param int $mode
     * @throws exceptions\RuntimeException
     * @return self
     */
    public function ensureDirectory($dir, $mode = null)
    {
        if (!is_dir($dir) && !mkdir($dir, $mode? : 0755, true)) {
            throw new Exception\RuntimeException(sprintf('Failed to create directory: "%s"', $dir));
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\FileSystemInterface::delete()
     */
    public function delete($fileOrDirectory)
    {
        if (!is_dir($fileOrDirectory) || is_link($fileOrDirectory)) {
            return unlink($fileOrDirectory);
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fileOrDirectory), RecursiveIteratorIterator::CHILD_FIRST);

        /* @var $fileInfo \SplFileInfo */
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir() && !$fileInfo->isLink()) {
                if (!rmdir($fileInfo->getPathname())) {
                    return false;
                }

                continue;
            }

            if (!unlink($fileInfo->getPathname())) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\FileSystemInterface::purgeDirectory()
     */
    public function purgeDirectory($dir)
    {
        $iterator = new DirectoryIterator($dir);

        foreach ($iterator as $file) {
            if (in_array($file->getFilename(), ['.', '..'])) {
                continue;
            }

            $this->delete($file->getPathname());
        }

        return $this;
    }
}
