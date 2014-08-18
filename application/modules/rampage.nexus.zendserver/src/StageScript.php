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

namespace rampage\nexus\zs;

use rampage\nexus\Executable;
use rampage\nexus\entities\ApplicationInstance;


class StageScript extends Executable
{
    /**
     * @param string $file
     * @param ApplicationInstance $application
     * @param Config $options
     * @param array $variables
     */
    public function __construct($file, ApplicationInstance $application, Config $options, array $variables = array())
    {
        parent::__construct('php');

        $this->addArg('-f')
            ->addArg($file);

        $this->setEnv('ZS_WEBSERVER_TYPE', $options->getWebserverType())
            ->setEnv('ZS_WEBSERVER_VERSION', $options->getWebserverVersion())
            ->setEnv('ZS_WEBSERVER_UID', $options->getWebserverUserId())
            ->setEnv('ZS_WEBSERVER_GID', $options->getWebserverGroupId())
            ->setEnv('ZS_PHP_VERSION', $options->getPHPVersion())
            ->setEnv('ZS_APPLICATION_BASE_DIR', $application->getDeployStrategy()->getTargetDirectory())
            ->setEnv('ZS_CURRENT_APP_VERSION', $application->getCurrentVersion()->getVersion())
            ->setEnv('ZS_PREVIOUS_APP_VERSION', $application->getPreviousVersion()->getVersion())
            ->setEnv('ZS_RUN_ONCE_NODE', ($options->isRunOnceNode())? '1' : '0');

        foreach ($variables as $name => $value) {
            $this->setEnv($this->prepareParamName($name, false), $value);
        }

        foreach ($application->getCurrentVersion()->getUserParameters(true) as $param => $value) {
            $param = $this->prepareParamName($param);
            $this->setEnv($param, $value);
        }
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
