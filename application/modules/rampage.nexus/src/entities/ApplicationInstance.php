<?php
/**
 * This is part of rampage-nexus
 * Copyright (c) 2013 Axel Helmert
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
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus\entities;

use rampage\nexus\ApplicationPackageInterface;

use Doctrine\ORM\Mapping as orm;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @orm\Entity
 */
class ApplicationInstance
{
    const STATE_DEPLOYED = 'deployed';
    const STATE_PENDING = 'pending';
    const STATE_STAGING = 'staging';
    const STATE_ACTIVATING = 'activating';
    const STATE_REMOVING = 'removing';
    const STATE_DEACTIVATING = 'deactivating';

    /**
     * @orm\Id @orm\Column(type="integer") @orm\GeneratedValue
     * @var int
     */
    protected $id = null;

    /**
     * @orm\Column(type="string", nullable=false)
     * @var string
     */
    protected $name = null;

    /**
     * @orm\Column(type="string", nullable=false)
     * @var string
     */
    protected $state = self::STATE_PENDING;

    /**
     * @orm\Column(type="blob", nullable=true)
     * @var resource
     */
    protected $icon = null;

    /**
     * @orm\Column(type="string", nullable=false)
     * @var string
     */
    protected $applicationName = null;

    /**
     * @orm\OneToMany(targetEntity="ApplicationVersion", cascade={"all"}, mappedBy="application", indexBy="version")
     * @var ArrayCollection|UserParameter[]
     */
    protected $versions = null;

    /**
     * @orm\ManyToOne(targetEntity="ApplicationVersion")
     * @var ApplicationVersion
     */
    protected $currentVersion = null;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->configTemplates = new ArrayCollection();
        $this->userParameters = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param ApplicationPackageInterface $package
     */
    public function updateFromApplicationPackage(ApplicationPackageInterface $package)
    {
        $this->applicationName = $package->getName();
        $icon = $package->getIcon();

        if ($icon !== false) {
            $this->setIcon($icon);
        }
    }

    /**
     * @param SplFileInfo|PharFileInfo|resource|string|null $icon
     * @return self
     */
    public function setIcon($icon)
    {
        if (($icon === null) || ($icon === false)) {
            $this->icon = null;
            return $this;
        }

        if (is_resource($this->icon)) {
            fclose($this->icon);
            $this->icon = null;
        }

        if (is_resource($icon)) {
            $this->icon = $icon;
            return $this;
        }

        if ($icon instanceof \PharFileInfo) {
            $this->icon = fopen('php://temp', 'w+');

            fwrite($this->icon, $icon->getContent());
            fseek($this->icon, 0);

            return $this;
        }

        $path = ($icon instanceof \SplFileInfo)? $icon->getPathname() : $icon;
        $this->icon = fopen($path, 'r');

        if ($this->icon === false) {
            $this->icon = null;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getApplicationName()
    {
        return $this->applicationName;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return \rampage\nexus\DeployStrategyInterface
     */
    public function getDeployStrategy()
    {
        // TODO: Implement strategy interface
    }

    /**
     * @param ApplicationVersion $version
     */
    public function addVersion(ApplicationVersion $version)
    {
        $version->setApplication($this);
        $this->versions[$version->getVersion()] = $version;
    }

    /**
     * @return Ambigous <\Doctrine\Common\Collections\ArrayCollection, multitype:\rampage\nexus\entities\UserParameter >
     */
    public function getVersions()
    {
        return $this->versions;
    }

	/**
     * @return \rampage\nexus\entities\ApplicationVersion
     */
    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }

	/**
     * @param \rampage\nexus\entities\ApplicationVersion $version
     * @return self
     */
    public function setCurrentVersion(ApplicationVersion $version)
    {
        if (!isset($this->versions[$version->getVersion()])) {
            $this->addVersion($version);
        }

        $this->currentVersion = $version;
        return $this;
    }

    /**
     * Create a new application version that should be used as current version
     *
     * @param string $version
     * @param array|Traversable $params
     * @param ConfigTemplate[] $templates
     * @return ApplicationVersion
     */
    public function newVersion($version, $params = array(), $templates = array())
    {
        if (isset($this->versions[$version])) {
            $instance = $this->versions[$version];
        } else {
            $instance = new ApplicationVersion($version);
        }

        $current = $this->getCurrentVersion();
        if ($current) {
            $instance->setUserParameters($current->getUserParameters());

            foreach ($current->getConfigTemplates() as $template) {
                $instance->addConfigTemplate($template);
            }
        }

        $instance->addUserParameters($params);

        foreach ($templates as $template) {
            $instance->addConfigTemplate($template);
        }

        $this->setCurrentVersion($instance);
        return $instance;
    }
}
