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

namespace rampage\nexus\node\installer\zpk;

use rampage\core\ArrayConfig;


/**
 * ZPK Configuration
 */
class Config extends ArrayConfig
{
    /**
     * @return string
     */
    public function getScriptCommand()
    {
        return $this->get('installer.php_command', 'php');
    }

    /**
     * @return string
     */
    public function getDirCreateMode()
    {
        return $this->get('installer.dir_create_mode', 0755);
    }

    /**
     * @return string
     */
    public function getWebserverType()
    {
        $default = 'undefined';

        if (isset($_SERVER['SERVER_SOFTWARE'])) {
            @list($default) = explode('/', $_SERVER['SERVER_SOFTWARE']);
        }

        return $this->get('installer.webserver.type', $default);
    }

    public function getWebserverVersion()
    {
        $default = 'undefined';

        if (isset($_SERVER['SERVER_SOFTWARE'])) {
            @list(, $default) = explode('/', $_SERVER['SERVER_SOFTWARE']);
        }

        return $this->get('installer.webserver.version', $default);
    }

    /**
     * @return string
     */
    public function getPHPVersion()
    {
        return $this->get('installer.php.version', PHP_VERSION);
    }

    /**
     * @return int
     */
    public function getWebserverUserId()
    {
        return $this->get('installer.webserver.userid', posix_getuid());
    }

    /**
     * @return int
     */
    public function getWebserverGroupId()
    {
        return $this->get('installer.webserver.groupid', posix_getgid());
    }
}
