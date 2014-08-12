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

use Serializable;

/**
 * Interface for web configuration instances
 */
interface WebConfigInterface
{
    /**
     * @param entities\ApplicationInstance $instance
     */
    public function setApplication(entities\ApplicationInstance $instance);

    /**
     * Returns the form containing the options
     *
     * @return \Zend\Form\FormInterface
     */
    public function getOptionsForm();

    /**
     * Set options for this web config
     *
     * @param array $options
     * @return self
     */
    public function setOptions(array $options);

    /**
     * Deactivate the current config for maintenance
     */
    public function maintenance();

    /**
     * @param DeployStrategyInterface $strategy
     */
    public function configure(DeployStrategyInterface $strategy);

    /**
     * Activate the current config
     */
    public function activate();

    /**
     * Remove the current config
     */
    public function remove();

    /**
     * @param array $options
     * @param ConfigTemplateLocator $templateLocator
     */
    public static function factory(array $options, ConfigTemplateLocator $templateLocator = null);
}
