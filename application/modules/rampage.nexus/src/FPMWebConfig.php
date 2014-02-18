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

use RuntimeException;

class FPMWebConfig implements WebConfigInterface
{
    /**
     * @var string
     */
    protected $serviceCommand = 'service php-fpm';

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var entities\ApplicationInstance
     */
    protected $application = null;

    /**
     * @param string $action
     * @throws RuntimeException
     * @return \rampage\nexus\FPMWebConfig
     */
    protected function serviceControl($action)
    {
        $cmd = $this->serviceCommand . ' ' . escapeshellarg($action);
        $return = 1;
        $out = array();

        exec($cmd, $out, $return);

        if ($return != 0) {
            throw new RuntimeException('Failed to invoke service action ' . $action . ":\n" . implode("\n", $out));
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function createPoolConfig()
    {
        // TODO: create pool config
    }

    /**
     * @return string
     */
    protected function createMaintenanceConfig()
    {
        // TODO: create maintenance config
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\WebConfigInterface::activate()
     */
    public function activate()
    {
        $this->serviceControl('reload');
    }

	/**
     * {@inheritdoc}
     * @see \rampage\nexus\WebConfigInterface::configure()
     */
    public function configure($documentRoot)
    {
        // TODO Auto-generated method stub

    }

	/**
     * {@inheritdoc}
     * @see \rampage\nexus\WebConfigInterface::maintenance()
     */
    public function maintenance()
    {
        // TODO Auto-generated method stub
        $this->serviceControl('reload');
    }

	/**
     * {@inheritdoc}
     * @see \rampage\nexus\WebConfigInterface::remove()
     */
    public function remove()
    {
        // TODO Auto-generated method stub
        $this->serviceControl('reload');
    }

	/**
     * {@inheritdoc}
     * @see \rampage\nexus\WebConfigInterface::setApplication()
     */
    public function setApplication(entities\ApplicationInstance $instance)
    {
        $this->application = $instance;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @see Serializable::serialize()
     */
    public function serialize()
    {
        return json_encode($this->options);
    }

	/**
     * {@inheritdoc}
     * @see Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        $this->options = json_decode($serialized, true);
        return $this;
    }
}
