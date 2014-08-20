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
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus;

use FilesystemIterator;
use RuntimeException;
use Exception;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateTrait;


/**
 * The default deployment strategy implementation
 */
class DefaultDeployStrategy implements DeployStrategyInterface
{
    use traits\WebConfigAwareTrait;
    use ListenerAggregateTrait;

    /**
     * @var entities\ApplicationInstance
     */
    protected $application = null;

    /**
     * @var string
     */
    protected $webRoot = null;

    /**
     * @var string
     */
    protected $previousDir = null;

    /**
     * @var string
     */
    protected $baseDirFormat = '/var/deployment/%appname%';

    /**
     * @var string
     */
    protected $dirFormat = '%version%';

    /**
     * @var string
     */
    protected $symlinkFormat = 'current';

    /**
     * @var Filesystem
     */
    protected $filesystem = null;


    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * {@inheritdoc}
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $this->attach(DeployEvent::EVENT_ACTIVATE, array($this, 'activate'));
        $this->listeners[] = $this->attach(DeployEvent::EVENT_DEACTIVATE, array($this, 'deactivate'));
        $this->listeners[] = $this->attach(DeployEvent::EVENT_STAGE, array($this, 'stage'));
        $this->listeners[] = $this->attach(DeployEvent::EVENT_REMOVE, array($this, 'remove'));
    }

    /**
     * @param DeployEvent $event
     * @return self
     */
    protected function injectDependenciesFromEvent(DeployEvent $event)
    {
        $event->getPackage()->setDeployStrategy($this);

        $this->setApplication($event->getApplication());
        $this->setWebRoot($event->getPackage()->getWebRoot());
        $this->setWebConfig($event->getWebConfig());

        return $this;
    }

    /**
     * @param entities\ApplicationInstance $application
     * @return self
     */
    public function setApplication(entities\ApplicationInstance $application)
    {
        $this->application = $application;
        return $this;
    }

    /**
     * @return boolean
     */
    protected function isConsole()
    {
        return $this->application->isConsoleApplication();
    }

    /**
     * @param string $format
     * @return string
     */
    protected function formatDir($format = null, array $additionalParams = array())
    {
        $dir = $format;

        if (!$format || (substr($format, 0, 1) != '/')) {
            $dir = $this->baseDirFormat;

            if ($format) {
                $dir .= '/' . $format;
            }
        }

        $params = array(
            'appid' => $this->application->getId(),
            'appname' => $this->application->getName(),
            'version' => $this->application->getCurrentVersion()->getVersion(),
        );

        $params = array_merge($params, $additionalParams);

        foreach ($params as $key => $value) {
            $dir = str_replace('%' . $key . '%', $value, $dir);
        }

        return $dir;
    }

    /**
     * @return string|null
     */
    private function lastErrorMessage()
    {
        return (new LastPhpError())->getMessage();
    }

    /**
     * @param string $dir
     */
    protected function deltree($dir)
    {
        $this->filesystem->delete($dir);
        return $this;
    }

    /**
     * Returns the full qulified path to the document root
     *
     * @return string
     */
    public function getWebRoot()
    {
        $root = $this->getTargetDirectory();

        if ($this->webRoot) {
            $root .= '/' . $this->webRoot;
        }

        return $root;
    }

    /**
     * @see \rampage\nexus\DeployStrategyInterface::getTargetDirectory()
     */
    public function getTargetDirectory()
    {
        return $this->formatDir($this->dirFormat);
    }

    /**
     * @return string
     */
    public function getBaseDir()
    {
        return $this->formatDir();
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\DeployStrategyInterface::getApplication()
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @see \rampage\nexus\DeployStrategyInterface::setWebRoot()
     */
    public function setWebRoot($dir)
    {
        $this->webRoot = $dir? trim($dir, '/') : $dir;
        return $this;
    }

    /**
     * @return self
     */
    protected function removeOldVersionDirectories()
    {
        $keepVersions = array($this->application->getCurrentVersion()->getVersion());

        if ($previous = $this->application->getPreviousVersion()) {
            $keepVersions[] = $previous->getVersion();
        }

        foreach ($this->application->getVersions() as $version) {
            if (in_array($version->getVersion(), $keepVersions)) {
                continue;
            }

            $oldDir = $this->formatDir($this->dirFormat, array('version' => $version->getVersion()));
            $this->deltree($oldDir);
        }

        return $this;
    }

    /**
     * @param DeployEvent $event
     */
    public function stage(DeployEvent $event)
    {
        $this->injectDependenciesFromEvent($event);

        $targetDir = $this->getTargetDirectory();

        if (is_dir($targetDir)) {
            // Deploying the same version again!
            $this->getWebConfig()->maintenance($this);
            $this->previousDir = dirname($targetDir) . '/' . basename($targetDir) . '_previous';

            $this->deltree($this->previousDir);
            rename($targetDir, $this->previousDir);
            unlink($this->formatDir($this->symlinkFormat));
        }

        mkdir($this->getTargetDirectory(), 0775, true);
        $event->getPackage()->install($this->application);
        $this->removeOldVersionDirectories();
    }

    /**
     * @param DeployEvent $event
     * @return self
     */
    public function remove(DeployEvent $event)
    {
        $this->injectDependenciesFromEvent($event);

        $this->getWebConfig()->remove($this);
        $event->getPackage()->remove($this->application);

        $this->deltree($this->formatDir());

        return $this;
    }

    /**
     * @event EVENT_ACTIVATE
     * @param DeployEvent $event
     */
    public function activate(DeployEvent $event)
    {
        $this->injectDependenciesFromEvent($event);

        $symlink = $this->formatDir($this->symlinkFormat);

        @unlink($symlink);
        symlink($this->getTargetDirectory(), $symlink);

        $webConfig = $this->getWebConfig();
        $webConfig->configure($this);
        $webConfig->activate($this);
    }

    /**
     * @param DeployEvent $event
     * @return self
     */
    public function deactivate(DeployEvent $event)
    {
        $this->injectDependenciesFromEvent($event);

        $this->getWebConfig()->maintenance($this);
        @unlink($this->formatDir($this->symlinkFormat));
    }
}
