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

namespace Rampage\Nexus\Node\ConfigTemplate;

/**
 * Interface for locating templates
 */
interface TemplateLocatorInterface
{
    /**
     * Returns the template for a specific config
     *
     * @param   string  $domain The template domain
     * @param   string  $name   The template name to locate
     * @param   string  $flavor The template flavor
     * @return  string          The template content as string
     */
    public function getConfigTemplate($domain, $name, $flavor = null);
}
