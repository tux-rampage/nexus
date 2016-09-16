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

use Psr\Http\Message\ResponseInterface;

class Artifact
{
    use ResourceTrait;

    /**
     * @var Build
     */
    private $build;

    /**
     * @param Build $build
     * @param array $data
     */
    public function __construct(Build $build, array $data, ClientInterface $api = null)
    {
        $this->build = $build;
        $this->_construct($data, $api);
    }

    /**
     * The related build
     *
     * @return \Rampage\Nexus\BuildSystem\Jenkins\Build
     */
    public function getBuild()
    {
        return $this->build;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->build->getUrl() . 'artifact/' . $this->getRelativePath();
    }

    /**
     * @return string
     */
    public function getContents()
    {
        return $this->getApi()
                    ->downloadArtifact($this)
                    ->getBody()
                    ->getContents();
    }

    /**
     * Download the artifact to the given file
     *
     * @param string $file
     * @return ResponseInterface
     */
    public function download($file)
    {
        return $this->getApi()->downloadArtifact($this, $file);
    }

    /**
     * Returns the filename
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->properties->get('fileName');
    }

    /**
     * Returns the relative artifact path
     *
     * @return string
     */
    public function getRelativePath()
    {
        return $this->properties->get('relativePath');
    }
}
