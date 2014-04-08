<?php
/**
 * This is part of rampage-nexus
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

namespace rampage\nexus;

class ConfigTemplate
{
    /**
     * @var string
     */
    protected $template = null;

    /**
     * @var array
     */
    protected $vars = array();

    /**
     * @param string $template
     */
    public function __construct($template)
    {
        $this->template = $template;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function set($name, $value)
    {
        $this->vars[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (!isset($this->vars[$name])) {
            return $default;
        }

        return $this->vars[$name];
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->vars[$name]);
    }

    /**
     * @param string $name
     */
    public function __unset($name)
    {
        unset($this->vars[$name]);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * @param array $variables
     * @return self
     */
    public function setVariables(array $variables)
    {
        $this->vars = $variables;
        return $this;
    }

    /**
     * @return string
     */
    public function render()
    {
        $content = $this->template;

        foreach ($this->vars as $key => $value) {
             $content = str_replace('$' . $key, $value, $content);
        }

        return $content;
    }
}
