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

namespace Rampage\Nexus\Package;

trait VersionStabilityTrait
{
    /**
     * Implements the stability check
     *
     * Utilizes the package version and analyzes it for unstable
     * markers which are:
     *
     *  - A develoment branch beginning with "dev-"
     *  - A semantic version number denoted with dev, alpha, beta or rc
     *    i.e. 1.0.0-rc1
     *  - A semantic version with major version 0
     *
     * @return bool
     */
    public function isStable()
    {
        if (!$this instanceof PackageInterface) {
            return false;
        }

        $version = $this->getVersion();
        return (bool)preg_match('/^dev-|^(\d+\.)*\d+-(dev|alpha|beta|rc)\.?\d*(\+([a-z0-9]+\.)?[a-z0-9]+)?$|^0\.\d/i', $version);
    }
}
