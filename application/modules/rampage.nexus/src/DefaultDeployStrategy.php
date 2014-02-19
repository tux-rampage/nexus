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

/**
 * The default deployment strategy implementation
 */
class DefaultDeployStrategy implements DeployStrategyInterface
{
    /**
     * @var string
     */
    protected $webRoot = null;

    /**
     * @var array
     */
    protected $userParams = array();

    /**
     * @var entities\ApplicationInstance
     */
    protected $application = null;

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
     * @param string $format
     * @return string
     */
    protected function formatDir($format = null)
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
        $last = error_get_last();
        return (isset($last['message']))? $last['message'] : '';
    }

    /**
     * @param string $dir
     */
    protected function deltree($dir)
    {
        if (!is_dir($dir)) {
            if (is_file($dir) && !@unlink($dir)) {
                throw new RuntimeException(sprintf(
                    'Could not delete file "%s": %s',
                    $dir, $this->lastErrorMessage()
                ));
            }

            return $this;
        }

        $iterator = new FilesystemIterator($dir, FilesystemIterator::CURRENT_AS_FILEINFO);

        /* @var $info \SplFileInfo */
        foreach ($iterator as $info) {
            if (in_array($info->getFilename(), array('.', '..'))) {
                continue;
            }

            if ($info->isDir()) {
                $this->deltree($info->getPathname());
                continue;
            }

            if (!@unlink($info->getPathname())) {
                throw new RuntimeException(sprintf(
                    'Could not delete file "%s": %s',
                    $info->getPathname(),
                    $this->lastErrorMessage()
                ));
            }
        }

        if (!@rmdir($dir)) {
            throw new RuntimeException(sprintf(
                'Could not delete file "%s": %s',
                $info->getPathname(),
                $this->lastErrorMessage()
            ));
        }

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
     * @return \rampage\nexus\WebConfigInterface
     */
    protected function getWebConfig()
    {
        return $this->application->getWebConfig();
    }

    /**
     * @see \rampage\nexus\DeployStrategyInterface::activate()
     */
    public function activate()
    {
        $symlink = $this->formatDir($this->symlinkFormat);

        @unlink($symlink);
        symlink($this->getTargetDirectory(), $symlink);

        if ($this->application->isConsoleApplication()) {
            return $this;
        }

        $webConfig = $this->getWebConfig();
        $webConfig->configure($this);
        $webConfig->activate();

        return $this;
    }

    /**
     * @see \rampage\nexus\DeployStrategyInterface::completeRemoval()
     */
    public function completeRemoval()
    {
        try {
            $this->deltree($this->formatDir());
        } catch (Exception $e) {
            // TODO: Implement logging
        }

        return $this;
    }

    /**
     * @see \rampage\nexus\DeployStrategyInterface::completeStaging()
     */
    public function completeStaging()
    {
        // TODO Manage persistent resources

        return $this;
    }

    /**
     * @see \rampage\nexus\DeployStrategyInterface::deactivate()
     */
    public function deactivate()
    {
        $symlink = $this->formatDir($this->symlinkFormat);
        @unlink($symlink);

        if (!$this->application->isConsoleApplication()) {
            $this->getWebConfig()->maintenance();
        }

        return $this;
    }

    /**
     * @see \rampage\nexus\DeployStrategyInterface::getTargetDirectory()
     */
    public function getTargetDirectory()
    {
        return $this->formatDir($this->dirFormat);
    }

    /**
     * @see \rampage\nexus\DeployStrategyInterface::prepareRemoval()
     */
    public function prepareRemoval()
    {
        if ($this->application->isConsoleApplication()) {
            return $this;
        }

        $this->getWebConfig()->remove();
        return $this;
    }

    /**
     * @see \rampage\nexus\DeployStrategyInterface::prepareStaging()
     */
    public function prepareStaging()
    {
        $targetDir = $this->getTargetDirectory();

        if (is_dir($targetDir)) {
            // Deploying the same version again!
            $this->application->getWebConfig()->maintenance();
            $this->previousDir = dirname($targetDir) . '/' . basename($targetDir) . '_previous';

            $this->deltree($this->previousDir);
            rename($targetDir, $this->previousDir);
        }

        mkdir($this->getTargetDirectory(), 0775, true);
        return $this;
    }

    /**
     * @see \rampage\nexus\DeployStrategyInterface::setApplicationInstance()
     */
    public function setApplicationInstance(entities\ApplicationInstance $instance)
    {
        $this->application = $instance;
        return $this;
    }

    /**
     * @see \rampage\nexus\DeployStrategyInterface::setUserParameters()
     */
    public function setUserParameters($parameters)
    {
        $this->userParams = $parameters;
        return $this;
    }

    /**
     * @see \rampage\nexus\DeployStrategyInterface::setWebRoot()
     */
    public function setWebRoot($dir)
    {
        $this->webRoot = $dir? trim($dir, '/') : $dir;
        return $this;
    }
}
