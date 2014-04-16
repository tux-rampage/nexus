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

namespace rampage\nexus\controllers;

use rampage\nexus\entities\Cluster;
use rampage\nexus\orm\DeploymentRepositoryAwareInterface;
use rampage\nexus\traits\DeploymentRepositoryAwareTrait;

use rampage\core\view\TemplateViewModel;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Http\Request as HttpRequest;

/**
 * @method HttpRequest getRequest()
 */
class ClusterController extends AbstractActionController implements DeploymentRepositoryAwareInterface
{
    use DeploymentRepositoryAwareTrait;

    /**
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $view = new TemplateViewModel('rampage.nexus/cluster/index');
        $view->custers = $this->getDeploymentRepository()->findClusters();

        return $view;
    }

    /**
     * @throws \LogicException
     */
    protected function assertHttpRequest()
    {
        if (!$this->getRequest() instanceof HttpRequest) {
            throw new \LogicException('This controller action can only be handle HTTP requests.');
        }
    }

    /**
     * Cluster
     */
    public function createClusterAction()
    {
        $this->assertHttpRequest();

        $view = new TemplateViewModel('rampage.nexus/cluster/create');
        $cluster = new Cluster();
        $form = (new AnnotationBuilder())->createForm($cluster);
        $request = $this->getRequest();

        $form->bind($cluster);

        if ($request->isPost() && !$form->isValid()) {
            // FIXME: Implement cluster save
        }

        return $view;
    }
}