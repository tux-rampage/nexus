<?php
/**
 * This is part of rampage.php
 * Copyright (c) 2013 Axel Helmert
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
 * @category  library
 * @package   rampage.core
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus\options;

use Zend\Form\Annotation as form;

/**
 * @form\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @form\Options({label="PHP-FPM Options"})
 */
class FPMOptions
{
    /**
     * @form\Type("text")
     * @form\Required(true)
     * @form\Validator({"name":"rampage\nexus\validation\BindAddress"})
     * @form\Filter({"name":"StringTrim"})
     * @form\Options({"label":"Socket or Port"})
     * @var string
     */
    public $listen = '0.0.0.0:9000';

    /**
     * @form\Type("text")
     * @form\Required(false)
     * @form\Filter({"name" : "StringTrim"})
     * @form\Options({"label" : "Username"})
     * @var string
     */
    public $user = 'www-data';

    /**
     * @form\Type("checkbox")
     * @form\Required(false)
     * @form\Filter({"name": "Boolean", options: { "type" : { "string", "false", "integer" }}})
     * @form\Options({"label" : "Chroot to base directory"})
     * @var boolean
     */
    public $chroot = false;

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
