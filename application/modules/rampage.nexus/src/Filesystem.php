<?php
/**
 * Copyright (c) 2014 Axel Helmert
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
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus;

use RuntimeException;
use DirectoryIterator;


class Filesystem
{
    /**
     * @param string $path
     * @return string
     */
    public function normalize($path)
    {
        $segments = explode('/', $path);
        $result = array();
        $isAbsolute = (substr($path, 0, 1) == '/');

        foreach ($segments as $segment) {
            if (($segment == '') || ($segment == '.')) {
                continue;
            }

            if ($segment == '..') {
                array_pop($result);
                continue;
            }

            $result[] = $segment;
        }

        $normalized = implode('/', $result);

        if ($isAbsolute) {
            $normalized = '/' . $normalized;
        }

        return $normalized;
    }

    /**
     * Removed the given file or directory.
     *
     * Directories will be removed recursively.
     *
     * @param string $path
     */
    public function delete($path)
    {
        if (!is_dir($path)) {
            if (is_file($path) && !@unlink($path)) {
                throw new RuntimeException(sprintf(
                    'Could not delete file "%s": %s',
                    $path, (new LastPhpError())->getMessage()
                ));
            }

            return $this;
        }

        $iterator = new DirectoryIterator($path);

        /* @var $info \SplFileInfo */
        foreach ($iterator as $info) {
            if (in_array($info->getFilename(), array('.', '..'))) {
                continue;
            }

            if ($info->isDir()) {
                $this->delete($info->getPathname());
                continue;
            }

            if (!@unlink($info->getPathname())) {
                throw new RuntimeException(sprintf(
                    'Could not delete file "%s": %s',
                    $info->getPathname(),
                    (new LastPhpError())->getMessage()
                ));
            }
        }

        if (!@rmdir($path)) {
            throw new RuntimeException(sprintf(
                'Could not delete file "%s": %s',
                $info->getPathname(),
                (new LastPhpError())->getMessage()
            ));
        }

        return $this;
    }
}
