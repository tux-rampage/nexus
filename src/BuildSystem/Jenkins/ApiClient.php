<?php
/**
 * Copyright (c) 2016 Axel Helmert
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
 * @copyright Copyright (c) 2016 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\BuildSystem\Jenkins;

use Rampage\Nexus\Exception\RuntimeException;
use Rampage\Nexus\Exception\UnexpectedValueException;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use Psr\Http\Message\ResponseInterface;


/**
 * Implements the api client
 */
class ApiClient
{
    /**
     * @var HttpClientInterface
     */
    private $http;

    /**
     * The jenkins URL
     *
     * @var string
     */
    private $url;

    /**
     * @param string $url
     * @param HttpClientInterface $http
     */
    public function __construct(HttpClientInterface $http)
    {
        $this->http = $http;
    }

    /**
     * Fetches JSON data from the jenkins API
     *
     * @param string $path
     * @throws UnexpectedValueException
     * @return array
     */
    private function getJson($path)
    {
        $response = $this->http->request('GET', $path . '/api/json');
        $contentType = $response->getHeaderLine('Content-Type');

        if (strpos($contentType, 'application/json') !== 0) {
            throw new UnexpectedValueException('Unexpected Content type: '  .$contentType);
        }

        $data = json_decode($response->getBody()->getContents(), true);
        $error = json_last_error();

        if ($error != JSON_ERROR_NONE) {
            throw new RuntimeException(json_last_error_msg(), $error);
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->http->getConfig('base_url');
    }

    /**
     * @param string $name
     * @return Project
     */
    public function getJob($name, Job $group = null)
    {
        $groupPath = $group? '/job/' . $group->getFullName() : '';
        $data = $this->getJson($groupPath . '/job/' . $name);

        return new Job($data, $group);
    }

    /**
     * @param Job $job
     * @param int $id
     */
    public function getBuild(Job $job, $id)
    {
        $data = $this->getJson('/job/' . $job->getFullName() . '/' . $id);
        return new Build($job, $data);
    }

    /**
     * Download the given artifact
     *
     * @param   Artifact    $artifact   The artifact to download
     * @param   string      $toFile     The file to download to. If omitted the content will be returned
     * @return  ResponseInterface       The HTTP response
     */
    public function downloadArtifact(Artifact $artifact, $toFile = null)
    {
        $build = $artifact->getBuild();
        $job = $build->getJob();
        $path = sprintf('/job/%s/%s/artifact/%s', $job->getFullName(), $build->getId(), $artifact->getRelativePath());
        $options = [];

        if ($toFile !== null) {
            $options['save_to'] = $toFile;
        }

        return $this->http->request('GET', $path, $options);
    }
}
