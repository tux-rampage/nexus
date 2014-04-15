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

namespace rampage\nexus\traits;

use Zend\Http\Client as HttpClient;
use Zend\Http\Client\Adapter\Curl as CurlHttpAdapter;

trait HttpClientAwareTrait
{
    /**
     * @var HttpClient
     */
    private $httpClient = null;

    /**
     * @return HttpClient
     */
    protected function getHttpClient()
    {
        if (!$this->httpClient) {
            if (method_exists($this, 'createHttpClient')) {
                $client = $this->createHttpClient();
            } else {
                $client = new HttpClient();
                $client->setAdapter(new CurlHttpAdapter());
            }

            $this->setHttpClient($client);
        }

        return $this->httpClient;
    }

    /**
     * @param HttpClient $httpClient
     * @return self
     */
    public function setHttpClient(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
        return $this;
    }
}
