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
     * @orm\ManyToMany(targetEntity="ConfigTemplate", cascade={"all"}, indexBy="role")
     * @orm\JoinTable(
     *      name="application_config_templates",
     *      joinColumns={@orm\JoinColumn(name="application_id", referencedColumnName="id")}
     *      inverseJoinColumns={@orm\JoinColumn(name="template_id", referencedColumnName="id", unique=true}
     * )
     * @var ArrayCollection|ConfigTemplate[]
     */
    protected $configTemplates = null;

    /**
     * @orm\OneToMany(targetEntity="UserParameter", cascade={"all"}, mappedBy="application", indexBy="name")
     * @var ArrayCollection|UserParameter[]
     */
    protected $userParameters = null;

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
     * @param ConfigTemplate $template
     * @return \rampage\nexus\entities\ApplicationInstance
     */
    public function setAddConfigTemplate(ConfigTemplate $template)
    {
        $role = $template->getRole();
        $this->configTemplates[$role] = $template;

        return $this;
    }

    /**
     * @param string $role
     * @return null|ConfigTemplate[]
     */
    public function getConfigTemplate($role)
    {
        if (isset($this->configTemplates[$role])) {
            return $this->configTemplates[$role];
        }

        return null;
    }

    public function setUserParameters()
    {

    }

    /**
     * @param string $params
     * @return self
     */
    public function addUserParameters($params)
    {
        foreach ($params as $name => $value) {
            if (isset($this->userParameters[$name])) {
                $this->userParameters[$name]->setValue($value);
                continue;
            }

            $parameter = new UserParameter($name, $value);
            $parameter->setApplication($this);

            $this->userParameters[$name] = $parameter;
        }

        return $this;
    }

    /**
     * @param bool $asArray Return the parameters as array or as object collection
     * @return array|UserParameter[]
     */
    public function getUserParameters($asArray = true)
    {
        if (!$asArray) {
            return $this->userParameters;
        }

        $params = array();

        foreach ($this->userParameters as $param) {
            $params[$param->getName()] = $param->getValue();
        }

        return $params;
    }
}
