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

/**
 * Build resource
 */
class Build
{
    use ResourceTrait;

    /**
     * @var Job
     */
    private $job;

    /**
     * @var Artifact[]
     */
    private $artifacts = null;

    /**
     * @param Job $job
     * @param unknown $data
     */
    public function __construct(Job $job, $data, ClientInterface $api)
    {
        $this->job = $job;
        $this->_construct($data, $api);
    }

    /**
     * @return \Rampage\Nexus\BuildSystem\Jenkins\Job
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->properties->get('id');
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->properties->get('url');
    }

    /**
     * @return string
     */
    public function getResult()
    {
        return $this->properties->get('result');
    }

    /**
     * Returns if the build is currently running
     *
     * @return bool
     */
    public function isBuilding()
    {
        return $this->properties->get('building');
    }

    /**
     * Pending status
     *
     * Whether the build has not started or is currently in progress
     *
     * @return bool
     */
    public function isPending()
    {
        return ($this->isBuilding() || ($this->getResult() == 'NOT_BUILT'));
    }

    /**
     * @return boolean
     */
    public function isStable()
    {
        return ($this->properties->get('result') == 'SUCCESS');
    }

    /**
     * @return boolean
     */
    public function isUsable()
    {
        return in_array($this->properties->get('result'), ['SUCCESS', 'UNSTABLE']);
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp()
    {
        $ts = $this->properties->get('timestamp');
        return $ts? new \DateTime('@' . $ts) : null;
    }

    /**
     * Checks if there is an artifact for the given relative path
     *
     * When you need the artifact later a direct call to getArtifact() might be faster
     *
     * @param string $relativePath
     * @return boolean
     */
    public function hasArtifact($relativePath)
    {
        return ($this->getArtifact($relativePath) !== null);
    }

    /**
     * Find an artifact of this build by its relative path
     *
     * @param   string          $relativePath   The relative path of the artifact
     * @return  Artifact|null                   The artifact resource or null if there is no artifact with that path
     */
    public function getArtifact($relativePath)
    {
        foreach ($this->getArtifacts() as $artifact) {
            if ($artifact->getRelativePath() == $relativePath) {
                return $artifact;
            }
        }

        return null;
    }

    /**
     * @return Artifact[]
     */
    public function getArtifacts()
    {
        if ($this->artifacts === null) {
            $mapper = function($data) {
                return new Artifact($this, $data, $this->api);
            };

            $this->artifacts = array_map($mapper, $this->properties->get('artifacts', []));
        }

        return $this->artifacts;
    }
}
