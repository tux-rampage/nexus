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

use rampage\nexus\entities\ApplicationInstance;
use rampage\nexus\hydration\ApplicationInstanceHydrator;
use rampage\nexus\orm\DeploymentRepository;
use rampage\nexus\PackageStorage;

use Zend\Http\Response\Stream as StreamHttpResponse;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;


class MasterController extends AbstractActionController implements RestControllerInterface
{
    /**
     * @return DeploymentRepository
     */
    protected function getRepository()
    {
        return $this->getServiceLocator()->get(DeploymentRepository::class);
    }

    /**
     * @return PackageStorage
     */
    protected function getPackageStore()
    {
        return $this->getServiceLocator()->get(PackageStorage::class);
    }

    /**
     * Deliver package file to a node
     */
    public function fetchPackageFileAction()
    {
        $applicationId = $this->params('applicationId');
        $application = $this->getRepository()->find(ApplicationInstance::class, $applicationId);

        if (!$application || !$application->getCurrentVersion()) {
            return $this->notFoundAction();
        }

        $version = $application->getCurrentVersion();
        $package = $this->getPackageStore()->getPackageFile($version);

        if (!$package->exists()) {
            return $this->notFoundAction();
        }

        $response = new StreamHttpResponse();
        $response->setStream($package->resource('r'))
            ->getHeaders()
            ->addHeaderLine('Content-Type: application/vnd.rampage-deployment-package');

        $this->getEvent()->setResponse($response);
        return $response;
    }

    /**
     * @return \Zend\View\Model\JsonModel
     */
    public function fetchApplicationInfoAction()
    {
        $applicationId = $this->params('applicationId');
        $application = $this->getRepository()->find(ApplicationInstance::class, $applicationId);

        if (!$application || !$application->getCurrentVersion()) {
            return $this->notFoundAction();
        }

        $hydrator = new ApplicationInstanceHydrator($this->getRepository());
        $data = $hydrator->extract($application);

        return new JsonModel($data);
    }
}
