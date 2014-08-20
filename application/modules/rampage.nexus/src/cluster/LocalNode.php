<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2014 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 */

namespace rampage\nexus\cluster;

use rampage\nexus\DeployEvent;
use rampage\nexus\DeploymentConfig;

use rampage\nexus\entities\ApplicationInstance;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;

use Zend\ServiceManager\ServiceManagerAwareInterface;


class LocalNode implements NodeInterface, ServiceManagerAwareInterface, EventManagerAwareInterface
{
    use DeploymentDependenciesTrait;
    use EventManagerAwareTrait;

    /**
     * @var DeployEvent
     */
    protected $event;

    /**
     * @param DeploymentConfig $config
     */
    public function __construct(DeploymentConfig $config)
    {
        $this->event = new DeployEvent();
        $this->setDeploymentConfig($config);
    }

    protected function attachDefaultListeners()
    {
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\cluster\NodeInterface::getId()
     */
    public function getId()
    {
        return 1;
    }

    /**
     * @param ApplicationInstance $application
     * @param string $newState
     */
    protected function changeApplicationState(ApplicationInstance $application, $newState)
    {
        $application->setState($newState);
        $this->getDeploymentRepository()->flush($application);
    }

    /**
     * @param ApplicationInstance $application
     * @return EventManagerInterface
     */
    protected function prepareDispatch(ApplicationInstance $application)
    {
        if ($this->event->getApplication() === $application) {
            return $this;
        }

        $this->injectDeployEventDependencies($this->event, $application);

        $package = $this->event->getPackage();
        $events = $this->getEventManager();

        if ($package instanceof ListenerAggregateInterface) {
            $events->attach($package);
        }

        return $events;
    }

    /**
     * @param ApplicationInstance $application
     * @param string $eventName
     */
    protected function dispatch(ApplicationInstance $application, $eventName)
    {
        try {
            $events = $this->prepareDispatch($application);
            $strategy = $this->event->getDeployStrategy();

            $events->attach($strategy);
            $events->trigger($eventName, $this->event);
            $events->detach($strategy);
        } catch (\Exception $e) {
            $this->changeApplicationState($application, ApplicationInstance::STATE_ERROR);
            throw $e;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\cluster\NodeInterface::activate()
     */
    public function activate(ApplicationInstance $application)
    {
        $this->changeApplicationState($application, ApplicationInstance::STATE_ACTIVATING);
        $this->dispatch($application, DeployEvent::EVENT_ACTIVATE);
        $this->changeApplicationState($application, ApplicationInstance::STATE_DEPLOYED);
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\cluster\NodeInterface::deactivate()
     */
    public function deactivate(ApplicationInstance $application)
    {
        $this->changeApplicationState($application, ApplicationInstance::STATE_DEACTIVATING);
        $this->dispatch($application, DeployEvent::EVENT_DEACTIVATE);
        $this->changeApplicationState($application, ApplicationInstance::STATE_INACTIVE);
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\cluster\NodeInterface::stage()
     */
    public function stage(ApplicationInstance $application)
    {
        $this->changeApplicationState($application, ApplicationInstance::STATE_STAGING);
        $this->dispatch($application, DeployEvent::EVENT_STAGE);
        $this->changeApplicationState($application, ApplicationInstance::STATE_INACTIVE);
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\cluster\NodeInterface::remove()
     */
    public function remove(ApplicationInstance $application)
    {
        $this->changeApplicationState($application, ApplicationInstance::STATE_REMOVING);
        $this->dispatch($application, DeployEvent::EVENT_UNSTAGE);
        $this->changeApplicationState($application, ApplicationInstance::STATE_REMOVED);
    }
}
