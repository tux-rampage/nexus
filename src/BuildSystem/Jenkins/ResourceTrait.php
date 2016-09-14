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

use Rampage\Nexus\Config\ArrayConfig;
use ArrayObject;
use Rampage\Nexus\Exception\LogicException;


/**
 * Trait for implementing jenkins REST resources
 */
trait ResourceTrait
{
    /**
     * @var ArrayObject
     */
    private $data;

    /**
     * @var ArrayConfig
     */
    private $properties;

    /**
     * @var ApiClient
     */
    private $api;

    /**
     * @param array $data
     */
    private function _construct(array $data, ApiClient $api = null)
    {
        $this->api = $api;
        $this->data = new ArrayObject($data);
        $this->properties = new ArrayConfig($this->data);
    }

    /**
     * Sets the API client to use
     *
     * @param ApiClient $api
     * @return self
     */
    public function setApiClient(ApiClient $api)
    {
        $this->api = $api;
        return $this;
    }

    /**
     * Returns the api client
     *
     * @throws LogicException
     * @return \Rampage\Nexus\BuildSystem\Jenkins\ApiClient
     */
    protected function getApi()
    {
        if (!$this->api) {
            throw new LogicException('Cannot invoke api actions without an api instance');
        }

        return $this->api;
    }
}
