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

namespace Rampage\Nexus\Ansible\Entities;

trait VariablesTrait
{
    /**
     * @var array
     */
    protected $variables = [];

    /**
     * @return multitype:
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param array $variables
     * @return self
     */
    public function setVariables(array $variables)
    {
        $this->variables = $variables;
        return $this;
    }

    /**
     * Add variables
     *
     * @param array $valiables
     * @return \Rampage\Nexus\Ansible\Entities\Group
     */
    public function addVariables(array $valiables)
    {
        foreach ($valiables as $key => $value) {
            $this->variables[$key] = $value;
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function hasVariables()
    {
        return !empty($this->variables);
    }
}