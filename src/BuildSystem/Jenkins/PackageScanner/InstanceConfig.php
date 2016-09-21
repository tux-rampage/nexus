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

namespace Rampage\Nexus\BuildSystem\Jenkins\PackageScanner;

class InstanceConfig
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $jenkinsUrl;

    /**
     * @var bool
     */
    private $scanArtifactFiles = false;

    /**
     * @var string[]
     */
    protected $includeProjects = [];

    /**
     * @var string[]
     */
    protected $excludeProjects = [];

    /**
     * @param string $jenkinsUrl
     */
    public function __construct($id, $jenkinsUrl)
    {
        $this->id = $id;
        $this->jenkinsUrl = $jenkinsUrl;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getJenkinsUrl()
    {
        return $this->jenkinsUrl;
    }

    /**
     * Included project names
     *
     * @return string[]
     */
    public function getIncludeProjects()
    {
        return $this->includeProjects;
    }

    /**
     * Excluded project names
     *
     * @return string[]
     */
    public function getExcludeProjects()
    {
        return $this->excludeProjects;
    }

    /**
     * @param bool $flag
     * @return self
     */
    public function enableArtifactScan($flag = true)
    {
        $this->scanArtifactFiles = (bool)$flag;
        return $this;
    }

    /**
     * Checks if artifacts should be downloaded and scanned
     *
     * @return bool
     */
    public function isArtifactScanEnabled()
    {
        return $this->scanArtifactFiles;
    }

    /**
     * @param string $name
     * @return self
     */
    public function includeProject($name)
    {
        $this->includeProjects[] = (string)$name;
        return $this;
    }

    /**
     * @param string $name
     * @return self
     */
    public function excludeProject($name)
    {
        $this->excludeProjects[] = (string)$name;
        return $this;
    }
}