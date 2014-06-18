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

namespace rampage\nexus\hydration;

use rampage\nexus\entities\ApplicationInstance;
use rampage\nexus\entities\ApplicationVersion;
use rampage\nexus\entities\VirtualHost;
use rampage\nexus\entities\ConfigTemplate;

use rampage\nexus\orm\DeploymentRepository;

use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\Reflection as ReflectionHydrator;


class ApplicationInstanceHydrator implements HydratorInterface
{
    /**
     * @var DeploymentRepository
     */
    protected $repository;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * @var \ReflectionClass[]
     */
    protected static $classReflections = array();

    /**
     * @var \PropertyReflection[]
     */
    protected static $reflections = array();

    /**
     * @param DeploymentRepository $repository
     */
    public function __construct(DeploymentRepository $repository)
    {
        $this->repository = $repository;
        $this->hydrator = new ReflectionHydrator();
    }

    /**
     * @param object $object
     * @param string $property
     * @return \ReflectionProperty
     */
    protected function reflectProperty($object, $property)
    {
        $class = get_class($object);
        $index = $class . '::' . $property;

        if (isset(self::$reflections[$index])) {
            return self::$reflections[$index];
        }

        if (!isset(self::$reflections[$class])) {
            self::$classReflections[$class] = new \ReflectionClass($class);
        }

        $reflection = self::$classReflections[$class];
        if (!$reflection->hasProperty($property)) {
            throw new \RuntimeException('No such property: ' . $index);
        }

        /* @var $propReflection \ReflectionProperty */
        $propReflection = $reflection->getProperty($property);
        $propReflection->setAccessible(true);

        self::$reflections[$index] = $propReflection;

        return $propReflection;
    }

    /**
     * @param object $object
     * @param string $property
     */
    protected function extractProperty($object, $property)
    {
        return $this->reflectProperty($object, $property)->getValue($object);
    }

    /**
     * @param ConfigTemplate $template
     * @return array
     */
    protected function extractConfigTemplate(ConfigTemplate $template)
    {
        return [
            'id' => $template->getId(),
            'role' => $template->getRole(),
            'label' => $template->getLabel(),
            'template' => $template->getTemplate()
        ];
    }

    /**
     * @param ApplicationVersion $version
     * @return array
     */
    protected function extractVersion(ApplicationVersion $version)
    {
        $data = [
            'id' => $version->getId(),
            'version' => $version->getVersion(),
            'userParameters' => $version->getUserParameters(true),
            'configTemplates' => []
        ];

        foreach ($version->getConfigTemplates() as $template) {
            $data['configTemplates'][] = $this->extractConfigTemplate($template);
        }

        return $data;
    }

    /**
     * @param VirtualHost $vhost
     * @return array
     */
    protected function extractVhost(VirtualHost $vhost)
    {
        $data = [
            'id' => $vhost->getId(),
            'serverName' => $vhost->getServerName(),
            'aliases' => $vhost->getAliases(),
            'port' => $vhost->getPort(),
            'sslCertFile' => $vhost->getSslCertFile(),
            'sslChainFile' => $vhost->getSslChainFile(),
            'sslKeyFile' => $vhost->getSslKeyFile(),
            'configTemplates' => []
        ];

        foreach ($vhost->getConfigTemplates() as $template) {
            $data['configTemplates'][] = $this->extractConfigTemplate($template);
        }

        return $data;

    }

    /**
     * {@inheritdoc}
     * @see \Zend\Stdlib\Extractor\ExtractionInterface::extract()
     */
    public function extract($application)
    {
        if (!$application instanceof ApplicationInstance) {
            return [];
        }

        $currentVersion = $application->getCurrentVersion();
        $data = [
            'id'  => $application->getId(),
            'applicationName' => $application->getApplicationName(),
            'baseUrl' => $application->getBaseUrl()->toString(),
            'currentVersion' => $currentVersion? $this->extractVersion($currentVersion) : null,
            'deployStrategy' => $this->extractProperty($application, 'deployStrategy'),
            'name' => $application->getName(),
            'packageType' => $application->getPackageType(),
            'vhost' => $this->extractVhost($application->getVirtualHost()),
        ];

        return $data;
    }

    /**
     * @param array $data
     * @return ConfigTemplate
     */
    protected function hydrateConfigTemplate($data)
    {
        $entityManager = $this->repository->getEntityManager();
        $template = $this->repository->find(ConfigTemplate::class, $data['id']);

        if (!$template) {
            $template = new ConfigTemplate();
            $entityManager->persist($template);
        }

        $this->hydrator->hydrate($data, $template);
        $entityManager->flush($template);

        return $template;
    }

    /**
     * @param array $data
     */
    protected function hydrateVersion(array $data)
    {
        $version = $this->repository->find(ApplicationVersion::class, $data['id']);
        $templates = $data['configTemplates'];
        $parameters = $data['userParameters'];
        unset($data['configTemplates'], $data['userParameters']);

        if (!$version) {
            $version = new ApplicationVersion();
        }

        $this->hydrator->hydrate($data, $version);

        $version->setUserParameters($parameters)
            ->getConfigTemplates()
            ->clear();

        foreach ($templates as $templateData) {
            $version->getConfigTemplates()->add($this->hydrateConfigTemplate($templateData));
        }

        return $version;
    }

    /**
     * @param array $data
     * @return VirtualHost
     */
    protected function hydrateVhost(array $data)
    {
        $entityManager = $this->repository->getEntityManager();
        $vhost = $this->repository->find(VirtualHost::class, $data['id']);
        $templates = $data['configTemplates'];

        if (!$vhost) {
            $vhost = new VirtualHost();
            $entityManager->persist($vhost);
        }

        unset($data['configTemplates']);

        $this->hydrator->hydrate($data, $vhost);
        $vhost->getConfigTemplates()->clear();

        foreach ($templates as $templateData) {
            $vhost->getConfigTemplates()->add($this->hydrateConfigTemplate($templateData));
        }

        $entityManager->flush($vhost);

        return $vhost;
    }

    /**
     * {@inheritdoc}
     * @see \Zend\Stdlib\Hydrator\HydrationInterface::hydrate()
     */
    public function hydrate(array $data, $object)
    {
        if (!$object instanceof ApplicationInstance) {
            throw new \UnexpectedValueException('Object must be an application instance!');
        }

        $vhost = $data['vhost'];
        $currentVersion = $data['currentVersion'];

        unset($data['vhost'], $data['currentVersion'], $data['versions']);

        $hydrator = new ReflectionHydrator();
        $hydrator->hydrate($data, $object);

        $object->getVersions()->clear();
        $object->setCurrentVersion($this->hydrateVersion($currentVersion));
        $object->setVirtualHost($this->hydrateVhost($vhost));

        return $object;
    }
}
