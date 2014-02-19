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

/**
 * FPM web configuration
 */
class NginxWebConfig implements WebConfigInterface, VHostCapableInterface
{
    protected $serviceCmd = 'service nginx';

    /**
     * @param string $action
     * @throws RuntimeException
     * @return self
     */
    protected function serviceControl($action)
    {
        $cmd = $this->serviceCmd . ' ' . escapeshellarg($action);
        $result = 1;
        $output = array();

        exec($cmd, $output, $result);

        if ($result != 0) {
            throw new RuntimeException(sprintf(
                'Failed to invoke service action "%s": %s',
                $action, "\n" . implode("\n", $output)
            ));
        }

        return $this;
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
    public function configure(DeployStrategyInterface $strategy)
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

    }

	/**
     * {@inheritdoc}
     * @see \rampage\nexus\WebConfigInterface::remove()
     */
    public function remove()
    {
        // TODO Auto-generated method stub

    }

	/**
     * {@inheritdoc}
     * @see \rampage\nexus\WebConfigInterface::setApplication()
     */
    public function setApplication(\rampage\nexus\entities\ApplicationInstance $instance)
    {
        // TODO Auto-generated method stub

    }

	/**
     * {@inheritdoc}
     * @see Serializable::serialize()
     */
    public function serialize()
    {
        // TODO Auto-generated method stub

    }

	/**
     * {@inheritdoc}
     * @see Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        // TODO Auto-generated method stub

    }

	/**
     * {@inheritdoc}
     * @see \rampage\nexus\VHostCapableInterface::createVirtualHost()
     */
    public function createVirtualHost(\rampage\nexus\entities\VirtualHost $vhost)
    {
        // TODO Auto-generated method stub

    }

	/**
     * {@inheritdoc}
     * @see \rampage\nexus\VHostCapableInterface::removeVirtualHost()
     */
    public function removeVirtualHost(\rampage\nexus\entities\VirtualHost $vhost)
    {
        // TODO Auto-generated method stub

    }


}
