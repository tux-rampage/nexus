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

namespace rampage\nexus\cluster;

use rampage\nexus\PackageStorage;
use rampage\nexus\entities\ApplicationInstance;
use rampage\nexus\hydration\ApplicationInstanceHydrator;
use rampage\nexus\orm\DeploymentRepository;
use rampage\nexus\traits\HttpClientAwareTrait;

use Zend\Http\Request as HttpRequest;
use Zend\Json\Json;
use Zend\Uri\Http as HttpUri;


class ClusterManager
{
    use HttpClientAwareTrait;

    /**
     * @var string
     */
    protected $url = null;

    /**
     * @var DeploymentRepository
     */
    protected $repository = null;

    /**
     * @var PackageStorage
     */
    protected $packageStorage = null;

    /**
     * @var ApplicationInstanceHydrator
     */
    protected $hydrator = null;

    /**
     * @param string $url
     */
    public function __construct($url, DeploymentRepository $repository, PackageStorage $packageStorage)
    {
        $this->url = $url;
        $this->repository = $repository;
        $this->packageStorage = $packageStorage;
        $this->hydrator = new ApplicationInstanceHydrator($repository);

        $this->getHttpClient();
    }

    /**
     * @param string $path
     * @return HttpUri
     */
    protected function getApiUrl($path)
    {
        $uri = new HttpUri($this->url);
        $path = rtrim($uri->getPath()) . '/' . ltrim($path);

        return $uri->setPath($path);
    }

    /**
     * @param ApplicationInstance $application
     */
    public function syncApplication(ApplicationInstance $application)
    {
        $request = new HttpRequest();
        $request->setUri($this->getApiUrl('application/' . $application->getId()))
            ->setMethod(HttpRequest::METHOD_GET);

        $response = $this->httpClient->send($request);

        if (!$response->isSuccess()) {
            throw new \RuntimeException('Failed to load application info from master');
        }

        $data = Json::decode($response->getBody(), Json::TYPE_ARRAY);
        $this->hydrator->hydrate($data, $application);
        $this->repository->flush($application);

        return $application;
    }

    /**
     * @param ApplicationInstance $application
     * @throws \RuntimeException
     * @return self
     */
    public function downloadArchive(ApplicationInstance $application)
    {
        $archive = $this->packageStorage->getPackageFile($application->getCurrentVersion());

        if ($archive->exists()) {
            return $this;
        }

        $request = new HttpRequest();
        $request->setUri($this->getApiUrl('application/package/' . $application->getId()))
            ->setMethod(HttpRequest::METHOD_GET);

        $this->packageStorage->mkdir(dirname($archive->getRelativePath()));
        $this->httpClient->setStream($archive->getPathname());

        $response = $this->httpClient->send($request);

        $this->httpClient->setStream(false);

        if (!$response->isSuccess()) {
            throw new \RuntimeException('Failed to download application package from master');
        }

        return $this;
    }

    /**
     * @param ApplicationInstance $application
     * @return self
     */
    public function updateApplicationState(ApplicationInstance $application)
    {
        $request = new HttpRequest();
        $data = array(
            'state' => $application->getState()
        );

        $request->setUri($this->getApiUrl('application/state/' . $application->getId()))
            ->setMethod(HttpRequest::METHOD_PUT)
            ->setContent(Json::encode($data));

        // Fire and forget
        $this->httpClient->send($request);

        return $this;
    }
}
