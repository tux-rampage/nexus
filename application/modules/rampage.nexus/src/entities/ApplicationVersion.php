<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2014 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 */

namespace rampage\nexus\entities;

use Doctrine\ORM\Mapping as orm;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

/**
 * @orm\Entity
 * @orm\HasLifecycleCallbacks
 */
class ApplicationVersion
{
    /**
     * @orm\Id @orm\Column(type="integer") @orm\GeneratedValue
     * @var int
     */
    protected $id = null;

    /**
     * @orm\Column(type="string", nullable=false)
     * @var string
     */
    protected $version = null;

    /**
     * @orm\ManyToOne(targetEntity="ApplicationInstance", inversedBy="versions")
     * @orm\JoinColumn(name="application_id", referencedColumnName="id", nullable=false)
     * @var ApplicationInstance
     */
    protected $application = null;

    /**
     * @orm\ManyToMany(targetEntity="ConfigTemplate", cascade={"all"}, indexBy="role")
     * @orm\JoinTable(
     *      name="appversion_config_templates",
     *      joinColumns={@orm\JoinColumn(name="application_id", referencedColumnName="id")},
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
     * @var array
     */
    private $prePersistAggregates = null;

    /**
     * Construct
     */
    public function __construct($version = null)
    {
        $this->version = $version;
        $this->configTemplates = new ArrayCollection();
        $this->userParameters = new ArrayCollection();
    }

    /**
     * @orm\PrePersist
     */
    public function prePersist(LifecycleEventArgs $event)
    {
        if ($this->prePersistAggregates || $this->id) {
            return;
        }

        // Keep aggregates for new persisted items
        $this->prePersistAggregates = array($this->configTemplates, $this->userParameters);
    }

    /**
     * @orm\PostFlush
     */
    public function postFlush(PostFlushEventArgs $event)
    {
        if (!$this->prePersistAggregates || !$this->id) {
            return;
        }

        list($this->configTemplates, $this->userParameters) = $this->prePersistAggregates;
        $this->prePersistAggregates = null;

        // Flush this instance again WITH aggregates
        $event->getEntityManager()->flush($this);
    }

    /**
     * @return number
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \rampage\nexus\entities\ApplicationInstance
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param ApplicationInstance $application
     * @return self
     */
    public function setApplication(ApplicationInstance $application)
    {
        $this->application = $application;
        return $this;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     * @return self
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @param ConfigTemplate $template
     * @return \rampage\nexus\entities\ApplicationInstance
     */
    public function addConfigTemplate(ConfigTemplate $template)
    {
        $role = $template->getRole();
        $this->configTemplates[$role] = $template;

        return $this;
    }

    /**
     * @param string $role
     * @return null|ConfigTemplate
     */
    public function getConfigTemplate($role)
    {
        if (isset($this->configTemplates[$role])) {
            return $this->configTemplates[$role];
        }

        return null;
    }

    /**
     * @return ArrayCollection|ConfigTemplate
     */
    public function getConfigTemplates()
    {
        return $this->configTemplates;
    }

    /**
     * Replace all user parameters
     *
     * @param array|Traversable $params
     * @return \rampage\nexus\entities\ApplicationVersion
     */
    public function setUserParameters($params)
    {
        $this->userParameters->clear();
        $this->addUserParameters($params);

        return $this;
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
            $parameter->setApplicationVersion($this);

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
