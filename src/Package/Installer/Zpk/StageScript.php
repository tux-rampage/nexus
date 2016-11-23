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

namespace Rampage\Nexus\Package\Installer\Zpk;

use Rampage\Nexus\Executable;


/**
 * Stage script wrapper
 */
class StageScript extends Executable
{
    /**
     * @param string $file
     * @param Application $application
     * @param Config $options
     * @param array $variables
     */
    public function __construct($file, Config $options, array $params, $baseDir, $version, array $variables = array())
    {
        parent::__construct($options->getScriptCommand());

        $this->addArg('-f')
            ->addArg($file);

        foreach ($variables as $name => $value) {
            if (!is_string($name) || !is_scalar($value)) {
                continue;
            }

            $this->setEnv($name, (string)$value);
        }

        foreach ($params as $param => $value) {
            $param = $this->prepareParamName($param);
            $this->setEnv($param, $value);
        }

        $this->setEnv('ZS_WEBSERVER_TYPE', $options->getWebserverType())
            ->setEnv('ZS_WEBSERVER_VERSION', $options->getWebserverVersion())
            ->setEnv('ZS_WEBSERVER_UID', $options->getWebserverUserId())
            ->setEnv('ZS_WEBSERVER_GID', $options->getWebserverGroupId())
            ->setEnv('ZS_PHP_VERSION', $options->getPHPVersion())
            ->setEnv('ZS_APPLICATION_BASE_DIR', $baseDir)
            ->setEnv('ZS_CURRENT_APP_VERSION', $version)
            ->setEnv('ZS_RUN_ONCE_NODE', '0');
    }

    /**
     * @param bool $flag
     * @return self
     */
    public function setIsRunOnceNode($flag)
    {
        $this->setEnv('ZS_RUN_ONCE_NODE', ($flag)? '1' : '0');
    }

    /**
     * @param string $name
     * @param bool $uppercase
     * @return string
     */
    protected function prepareParamName($name, $uppercase = true)
    {
        $name = preg_replace('~[^a-z0-9_]+~i', '_', $name);
        $name = 'ZS_' . $name;

        if ($uppercase) {
            $name = strtoupper($name);
        }

        return $name;
    }
}
