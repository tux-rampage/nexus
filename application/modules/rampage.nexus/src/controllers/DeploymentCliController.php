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

namespace rampage\nexus\controllers;

use rampage\nexus\DeployStrategyManager;
use rampage\nexus\entities\Application;
use rampage\nexus\orm\DeploymentRepository;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;

use RuntimeException;
use rampage\nexus\DeploymentConfig;
use rampage\nexus\PackageInstallerManager;
use rampage\nexus\DeploymentNode;


class DeploymentCliController extends AbstractActionController
{
    /**
     * @var ApplicationInstance
     */
    protected $application = null;

    /**
     * @throws \DomainException
     */
    public function assertDeployNode()
    {
        $config = $this->getServiceLocator()->get('DeploymentConfig');

        if (!$config->isNode() && !$config->isStandalone()) {
            throw new \DomainException('Only nodes may perform local deployments.');
        }
    }

    /**
     * {@inheritdoc}
     * @see \Zend\Mvc\Controller\AbstractController::attachDefaultListeners()
     */
    protected function attachDefaultListeners()
    {
        parent::attachDefaultListeners();
        $this->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'assertDeployNode'), 1000);
    }

    /**
     * @return DeploymentConfig
     */
    public function getConfig()
    {
        return $this->getServiceLocator()->get('DeploymentConfig');
    }

    /**
     * @return PackageInstallerManager
     */
    public function getPackageInstallerManager()
    {
        return $this->getServiceLocator()->get(PackageInstallerManager::class);
    }

    /**
     * @return DeploymentNode
     */
    public function getDeploymentNode()
    {
        return $this->getServiceLocator()->get(DeploymentNode::class);
    }

    /**
     * @return boolean
     */
    protected function initApplication($prepare = true)
    {
        $appId = $this->params('application');
        if (!$appId) {
            return false;
        }

        /* @var $repository DeploymentRepository */
        $strategyManager = $this->getServiceLocator()->get(DeployStrategyManager::class);
        $repository = $this->getServiceLocator()->get(DeploymentRepository::class);

        if ($prepare) {
            $this->application = $this->getDeploymentNode()->prepareApplicationInstance($appId);
        } else {
            $this->application = $repository->findApplicationById($appId);
        }

        if (!$this->application) {
            return false;
        }

        $this->application->setDeployStrategyManager($strategyManager);
        return true;
    }

    /**
     * Deploy an application
     */
    public function deployAction()
    {
        if (!$this->initApplication()) {
            throw new RuntimeException(sprintf('Could not find application %d', $this->params('application')));
        }

        $this->getDeploymentNode()->deploy();
        $package = $this->getPackageInstallerManager()->getPackageInstaller($archive);



    }

    public function removeAction()
    {

    }
}
