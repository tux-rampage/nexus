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

namespace Rampage\Nexus\Bundle;

use Rampage\Nexus\Config\AbstractConfigProvider;
use Rampage\Nexus\Master\ConfigProvider as MasterConfigProvider;
use Rampage\Nexus\ODM\ConfigProvider as ODMConfigProvider;
use Rampage\Nexus\Ansible\ConfigProvider as AnsibleConfigProvider;
use Rampage\Nexus\Config\PhpDirectoryProvider;


class ConfigProvider extends AbstractConfigProvider
{
    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Config\AbstractConfigProvider::getGeneratedFilePath()
     */
    protected function getGeneratedFilePath()
    {
        return __DIR__ . '/../_generated/config.php';
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Config\AbstractConfigProvider::getProviders()
     */
    protected function getProviders()
    {
        return [
            MasterConfigProvider::class,
            ODMConfigProvider::class,
            AnsibleConfigProvider::class,
            new PhpDirectoryProvider(__DIR__ . '/../config'),
        ];
    }
}
