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

namespace rampage\nexus\node;

use rampage\nexus\entities\ApplicationInstance;
class NginxDeployStrategy implements DeployStrategyInterface
{
    const TEMPLATE_SERVER = 'server';
    const TEMPLATE_LOCATION = 'location';
    const TEMPLATE_GLOBAL = 'global';

    /**
     * @var config\TemplateLocatorInterface
     */
    protected $templateLocator;

    /**
     * @param config\TemplateLocatorInterface $templateLocator
     */
    public function __construct(config\TemplateLocatorInterface $templateLocator)
    {
        $this->templateLocator = $templateLocator;
    }

    /**
     * @see \rampage\nexus\node\DeployStrategyInterface::activate()
     */
    public function activate(ApplicationInstance $instance)
    {
        // TODO Auto-generated method stub

    }

    /**
     * @see \rampage\nexus\node\DeployStrategyInterface::prepareActivation()
     */
    public function prepareActivation(\rampage\nexus\entities\ApplicationInstance $instance)
    {
        // TODO Auto-generated method stub

    }

    /**
     * @see \rampage\nexus\node\DeployStrategyInterface::purge()
     */
    public function purge()
    {
        // TODO Auto-generated method stub

    }

    /**
     * @see \rampage\nexus\node\DeployStrategyInterface::remove()
     */
    public function remove(\rampage\nexus\entities\ApplicationInstance $instance)
    {
        // TODO Auto-generated method stub

    }

    /**
     * @see \rampage\nexus\node\DeployStrategyInterface::rollback()
     */
    public function rollback(\rampage\nexus\entities\ApplicationInstance $toInstance)
    {
        // TODO Auto-generated method stub

    }

    /**
     * @see \rampage\nexus\node\DeployStrategyInterface::stage()
     */
    public function stage(\rampage\nexus\entities\ApplicationInstance $instance)
    {
        // TODO Auto-generated method stub

    }



}