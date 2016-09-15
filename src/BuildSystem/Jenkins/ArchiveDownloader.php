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

use Rampage\Nexus\Archive\DownloaderInterface;
use Rampage\Nexus\Exception\InvalidArgumentException;

class ArchiveDownloader implements DownloaderInterface
{
    const URL_PATTERN = '~^jenkins://(?<id>[a-z0-9]+)/(?<job>.+)/build/(?<build>\d+)/(?<artifact>.+)$~i';

    /**
     * @var Repository\InstanceRepositoryInterface
     */
    private $instanceRepository;

    /**
     * @var ClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @param ClientFactoryInterface $clientFactory
     * @param Repository\InstanceRepositoryInterface $instanceRepository
     */
    public function __construct(ClientFactoryInterface $clientFactory, Repository\InstanceRepositoryInterface $instanceRepository)
    {
        $this->clientFactory = $clientFactory;
        $this->instanceRepository = $instanceRepository;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Archive\DownloaderInterface::canDownload()
     */
    public function canDownload($url)
    {
        return (bool)preg_match(self::URL_PATTERN, $url);
    }

    /**
     * @param string $url
     * @throws InvalidArgumentException
     * @return array
     */
    private function parseUrl($url)
    {
        $args = [];
        if (!preg_match(self::URL_PATTERN, $url, $args)) {
            throw new InvalidArgumentException('Invalid jenkins url: ' . $url);
        }

        return $args;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Archive\DownloaderInterface::download()
     */
    public function download($url, $targetFile)
    {
        $args = $this->parseUrl($url);
        $instance = $this->instanceRepository->find($args['id']);
        $client = $this->clientFactory->createJenkinsClient($instance->getJenkinsUrl());
        $job = $client->getJob($args['job']);
        $build = $job->getBuild($args['build']);
        $artifact = $build->getArtifact($args['artifact']);

        $client->downloadArtifact($artifact, $targetFile);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Archive\DownloaderInterface::getFilenameFromUrl()
     */
    public function getFilenameFromUrl($url)
    {
        $args = $this->parseUrl($url);

        return sprintf(
            '%s.%d.%s-$%s',
            $args['id'],
            $args['build'],
            md5($args['job']),
            basename($args['artifact'])
        );
    }
}
