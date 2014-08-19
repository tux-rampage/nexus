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

namespace rampage\nexus\package;

use rampage\nexus\DeployStrategyInterface;


abstract class AbstractApplicationPackage implements ApplicationPackageInterface
{
    /**
     * Package type name
     */
    const TYPE_NAME = '';

    /**
     * @var DeployStrategyInterface
     */
    protected $deployStrategy = null;

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\package\ApplicationPackageInterface::getTypeName()
     */
    public function getTypeName()
    {
        if (static::TYPE_NAME == '') {
            return get_class($this);
        }

        return static::TYPE_NAME;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\package\ApplicationPackageInterface::setDeployStrategy()
     */
    public function setDeployStrategy(DeployStrategyInterface $strategy)
    {
        $this->deployStrategy = $strategy;
        return $this;
    }
}
