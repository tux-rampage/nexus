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

namespace rampage\nexus\api\controllers;

use rampage\nexus\DeploymentConfig;
use rampage\nexus\DeploymentNode;

use rampage\nexus\orm\DeploymentRepository;
use rampage\nexus\entities\ApplicationInstance;

use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use rampage\core\exception\RuntimeException;


class NodeController extends AbstractActionController implements RestControllerInterface
{
    /**
     * @return DeploymentRepository
     */
    protected function getRepository()
    {
        return $this->getServiceLocator()->get(DeploymentRepository::class);
    }

    /**
     * @return \rampage\nexus\DeploymentNode
     */
    protected function getDeploymentNode()
    {
        return $this->getServiceLocator()->get(DeploymentNode::class);
    }

    /**
     * @return DeploymentConfig
     */
    protected function getConfig()
    {
        return $this->getServiceLocator()->get(DeploymentConfig::class);
    }

    /**
     * {@inheritdoc}
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        return new JsonModel(array(
            'name' => 'TODO',
        ));
    }

    /**
     * Attach node to master
     */
    public function attachAction()
    {
        if ($this->getConfig()->getMasterApiUrl()) {
            throw new RuntimeException('This node is already attached to a master server');
        }

    }

    public function detatchAction()
    {

    }

    public function deployAction()
    {



        $this->getDeploymentNode()->deploy($this->params('applicationId'));

    }

    public function removeAction()
    {
        /* @var $application \rampage\nexus\entities\ApplicationInstance */
        $application = $this->getRepository()->findApplicationByMasterId($this->params('applicationId'));
        if (!$application instanceof ApplicationInstance) {
            return $this->notFoundAction();
        }

        $this->getDeploymentNode()->remove($application);
    }

    public function statusAction()
    {
        /* @var $application \rampage\nexus\entities\ApplicationInstance */
        $application = $this->getRepository()->findApplicationById($this->params('applicationId'));

        if (!$application instanceof ApplicationInstance) {
            return $this->notFoundAction();
        }

        $json = new JsonModel(array(
            'id' => $application->getId(),
            'name' => $application->getName(),
            'version' => $application->getCurrentVersion()->getVersion(),
            'state' => $application->getState()
        ));

        return $json;
    }
}
