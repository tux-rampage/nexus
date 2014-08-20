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

namespace rampage\nexus;

/**
 * Stub web config for console applications
 */
class ConsoleWebConfig implements WebConfigInterface
{
    /**
     * @param array $options
     * @param ConfigTemplateLocator $templateLocator
     */
    public static function factory(array $options, ConfigTemplateLocator $templateLocator = null)
    {
        return new self();
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\WebConfigInterface::activate()
     */
    public function activate(entities\ApplicationInstance $application, DeployStrategyInterface $strategy)
    {
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\WebConfigInterface::configure()
     */
    public function configure(entities\ApplicationInstance $application, DeployStrategyInterface $strategy)
    {
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\WebConfigInterface::maintenance()
     */
    public function maintenance(entities\ApplicationInstance $application, DeployStrategyInterface $strategy)
    {
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\WebConfigInterface::remove()
     */
    public function remove(entities\ApplicationInstance $application, DeployStrategyInterface $strategy)
    {
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\WebConfigInterface::setOptions()
     */
    public function setOptions(array $options)
    {
    }
}
