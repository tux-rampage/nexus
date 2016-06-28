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

namespace Rampage\Nexus\Node;

use Rampage\Nexus\Deployment\StageSubscriberInterface;

use Rampage\Nexus\Package\Installer\InstallerProviderInterface;
use Rampage\Nexus\Package\PackageInterface;


/**
 * Abstract deploy strategy
 */
abstract class AbstractDeployStrategy implements DeployStrategyInterface
{
    /**
     * @var StageSubscriberList
     */
    protected $subscribers;

    /**
     * @var InstallerProviderInterface
     */
    protected $installerProvider;

    /**
     * @param InstallerProviderInterface $installerProvider
     */
    public function __construct(InstallerProviderInterface $installerProvider)
    {
        $this->installerProvider = $installerProvider;
        $this->subscribers = new StageSubscriberList();
    }

    /**
     * Add a stage subscriber
     *
     * @param StageSubscriberInterface $subscriber
     * @return self
     */
    public function addStageSubscriber(StageSubscriberInterface $subscriber)
    {
        $this->subscribers->add($subscriber);
        return $this;
    }

    /**
     * Remove a stage subscriber
     *
     * @param StageSubscriberInterface $subscriber
     * @return self
     */
    public function removeStageSubscriber(StageSubscriberInterface $subscriber)
    {
        $this->subscribers->remove($subscriber);
        return $this;
    }

    /**
     * @param InstallerInterface $installer
     * @return self
     */
    protected function getInstaller(PackageInterface $package)
    {
        return $this->installerProvider->getInstaller($package);
    }
}
