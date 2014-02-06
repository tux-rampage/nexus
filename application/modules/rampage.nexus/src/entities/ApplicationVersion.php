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

/**
 * @orm\Entity
 */
class ApplicationVersion
{
    /**
     * @orm\Id @orm\Column(type="integer") @orm\GeneratedValue
     * @var int
     */
    protected $id = null;

    /**
     * @orm\ManyToOne(targetEntity="ApplicationInstance", inversedBy="versions")
     * @var ArrayCollection|ApplicationInstance
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
     * Construct
     */
    public function __construct(ApplicationInstance $application = null)
    {
        $this->application = $application;
        $this->configTemplates = new ArrayCollection();
        $this->userParameters = new ArrayCollection();
    }

    /**
     * @return ArrayCollection|ConfigTemplate[]
     */
    public function getConfigTemplates()
    {
        return $this->configTemplates;
    }
}
