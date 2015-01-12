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

namespace rampage\nexus;

/**
 * Application Package Interface
 */
interface PackageInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getVersion();

    /**
     * Returns the package type
     */
    public function getType();

    /**
     * Returns the relative path to the document root
     *
     * @return string
     */
    public function getDocumentRoot();

    /**
     * Returns defined package parameters
     *
     * @return entities\PackageParameter[]
     */
    public function getParameters();

    /**
     * Returns extra package information
     *
     * @param string $name Omit to return all extra options
     * @return array|string
     */
    public function getExtra($name = null);
}
