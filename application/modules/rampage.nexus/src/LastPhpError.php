<?php
/**
 * This is part of rampage.php
 * Copyright (c) 2013 Axel Helmert
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
 * @category  library
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus;

class LastPhpError
{
    /**
     * @var array
     */
    protected $info = null;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->info = error_get_last();
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function info($key)
    {
        if (!isset($this->info[$key])) {
            return null;
        }

        return $this->info[$key];
    }

    /**
     * @return boolean
     */
    public function hasError()
    {
        return is_array($this->info);
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->info('message');
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->info('file');
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->info('line');
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->info('type');
    }
}
