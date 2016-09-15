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
use Rampage\Nexus\Exception\InvalidArgumentException;


/**
 * Implements the api client
 */
class RestClient implements ClientInterface
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
    private function getJson($path, $asArray = true)
    {
        $response = $this->http->request('GET', $path . '/api/json');
        $contentType = $response->getHeaderLine('Content-Type');

        if (strpos($contentType, 'application/json') !== 0) {
            throw new UnexpectedValueException('Unexpected Content type: '  .$contentType);
        }

        $data = json_decode($response->getBody()->getContents(), $asArray);
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
     * @param string[] $names
     */
    private function buildDummyGroups($names)
    {
        $group = null;

        while (count($names) > 0) {
            $name = array_shift($names);
            $drop = array_shift($names);

            if ($drop != 'job') {
                throw new InvalidArgumentException('Bad job name path semantics, expected "/job/"');
            }

            $group = new Job(['name' => $name], $group);
        }

        return $group;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\BuildSystem\Jenkins\ClientInterface::getJobs()
     */
    public function getJobs()
    {
        $data = $this->getJson('', false);

        if (!isset($data->jobs) || !is_array($data->jobs)) {
            return [];
        }

        $mapper = function($job) {
            return $job->name;
        };

        return array_map($mapper, $data->jobs);
    }

    /**
     * @param string $name
     * @return Job
     */
    public function getJob($name, Job $group = null)
    {
        if (strpos($name, '/') && !$group) {
            $names = explode('/', $name);
            $name = array_pop($names);
            $group = $this->buildDummyGroups($names);
        }

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
        $options = [
            'timeout' => 600
        ];

        if ($toFile !== null) {
            $options['save_to'] = $toFile;
        }

        return $this->http->request('GET', $path, $options);
    }
}
