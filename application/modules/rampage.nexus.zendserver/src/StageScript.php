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
    public function __construct($file, ApplicationInstance $application, Config $options)
    {
        parent::__construct('php');

        $this->addArg('-f')
            ->addArg($file);

        $this->setEnv('ZS_RUN_ONCE_NODE', '1')
            ->setEnv('ZS_WEBSERVER_TYPE', $options->getWebserverType())
            ->setEnv('ZS_WEBSERVER_VERSION', 'TODO')
            ->setEnv('ZS_WEBSERVER_UID', 'TODO')
            ->setEnv('ZS_WEBSERVER_GID', 'TODO')
            ->setEnv('ZS_PHP_VERSION', 'TODO')
            ->setEnv('ZS_APPLICATION_BASE_DIR', $application->getDeployStrategy()->getTargetDirectory())
            ->setEnv('ZS_CURRENT_APP_VERSION', $application->getCurrentVersion()->getVersion())
            ->setEnv('ZS_PREVIOUS_APP_VERSION', 'TODO');

        foreach ($application->getCurrentVersion()->getUserParameters(true) as $param => $value) {
            $param = strtoupper($param);
            $this->setEnv('ZS_'.$param, $value);
        }
    }
}
