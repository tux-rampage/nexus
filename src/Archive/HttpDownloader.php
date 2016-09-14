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

namespace Rampage\Nexus\Archive;

use GuzzleHttp\Client as HttpClient;
use Zend\Uri\Http as HttpUri;
use Throwable;


/**
 * Http downloader
 */
class HttpDownloader implements DownloaderInterface
{
    /**
     * @var HttpClient
     */
    private $client;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->client = new HttpClient([
            'defaults' =>  [
                'timeout' => 14400,
                'connect_timeout' => 4,
            ]
        ]);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Archive\DownloaderInterface::canDownload()
     */
    public function canDownload($url)
    {
        try {
            $uri = new HttpUri($url);
        } catch (Throwable $e) {
            return false;
        }

        return (basename($uri->getPath()) != '');
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Archive\DownloaderInterface::download()
     */
    public function download($url, $targetFile)
    {
        /* @var $response \Psr\Http\Message\ResponseInterface */
        $response = $this->client->get($url, [
            'save_to' => $targetFile
        ]);

        return ($response->getStatusCode() == 200);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Archive\DownloaderInterface::getFilenameFromUrl()
     */
    public function getFilenameFromUrl($url)
    {
        $uri = new HttpUri($url);
        return basename($uri->getPath());
    }
}
