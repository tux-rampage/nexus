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

namespace rampage\nexus;

use rampage\core\view\TemplateViewModel;
use rampage\core\controllers\ResourcesController;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

use Zend\Mvc\MvcEvent;


class DispatchListener implements ListenerAggregateInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use ListenerAggregateTrait;

    /**
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     */
    public function attach(\Zend\EventManager\EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'checkServerType'), 200);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'initLayout'), 100);
    }

    /**
     * @param MvcEvent $event
     */
    public function initLayout(MvcEvent $event)
    {

    }

    /**
     * @param MvcEvent $event
     */
    public function checkServerType(MvcEvent $event)
    {
        $config = $this->serviceLocator->get('DeploymentConfig');

        if (!$config->isNode()) {
            return;
        }

        if (($event->getTarget() instanceof api\controllers\RestControllerInterface)
            || ($event->getTarget() instanceof ResourcesController)) {
            return;
        }

        $view = new TemplateViewModel('rampage.nexus/errors/node-only-info');

        $event->getViewModel()->addChild($view, 'content');
        $event->stopPropagation(true);

        return $view;
    }

}
