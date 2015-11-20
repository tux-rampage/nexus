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

namespace rampage\nexus\node\installer;

use rampage\nexus\exceptions;
use rampage\nexus\package\ComposerPackage;

use Zend\Json\Json;

use rampage\nexus\PackageInterface;
use rampage\nexus\Executable;
use rampage\nexus\exceptions\StageScriptException;


/**
 * Implements the installer for composer packages
 */
class ComposerInstaller extends AbstractInstaller implements StageSubscriberInterface
{
    const TYPE_NAME = ComposerPackage::TYPE_COMPOSER;

    const STAGE_INSTALL         = 'stage';
    const STAGE_REMOVE          = 'remove';
    const STAGE_PRE_ACTIVATE    = 'pre-activate';
    const STAGE_POST_ACTIVATE   = 'post-activate';
    const STAGE_PRE_DEACTIVATE  = 'pre-deactivate';
    const STAGE_POST_DEACTIVATE = 'post-deactivate';
    const STAGE_PRE_ROLLBACK    = 'pre-rollback';
    const STAGE_POST_ROLLBACK   = 'post-rollback';

    /**
     * @var PackageInterface
     */
    protected $package;

    /**
     * @see \rampage\nexus\package\InstallerInterface::getPackage()
     */
    public function getPackage()
    {
        if ($this->package !== null) {
            return $this->package;
        }

        if (!isset($this->archive['composer.json'])) {
            throw new exceptions\RuntimeException(sprintf('Could not find composer.json in package file "%s"', $this->archive->getAlias()));
        }

        $json = Json::decode($this->archive['composer.json']->getContents(), Json::TYPE_ARRAY);

        if (isset($json['extra']['deployment']) && is_string($json['extra']['deployment'])) {
            $deploymentFile = $json['extra']['deployment'];

            if (!isset($this->archive[$deploymentFile])) {
                throw new exceptions\RuntimeException(sprintf(
                    'Could not find referenced deployment file "%s" in package file "%s"',
                    $this->archive->getAlias()
                ));
            }

            $json['extra']['deployment'] = Json::decode($this->archive[$deploymentFile]->getContents(), Json::TYPE_ARRAY);
        }

        $this->package = new ComposerPackage($json);
        return $this->package;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\package\ApplicationPackageInterface::getTypeName()
     */
    public function getTypeName()
    {
        return static::TYPE_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getWebRoot($params)
    {
        $this->assertTargetDirectory();
        $path = $this->targetDirectory->getPathname();

        if ($root = $this->getPackage()->getDocumentRoot()) {
            $path = rtrim($path, '/') . '/' . ltrim($root, '/');
        }

        return $path;
    }

    /**
     * Calls the deploy script of this package (if it exists)
     *
     * If this (shell) call results in a non-zero response, an axception is thrown.
     *
     * @throws exceptions\StageScriptException  Thrown if the stage script exited with non-zero
     * @param  string  $type    The script type to trigger
     * @param  array   $params  User parameters
     * @param  array   $env     Additional environment variables
     */
    protected function triggerDeployScript($type, $params, array $env = [])
    {
        $dir = $this->getPackage()->getExtra('scripts_dir');
        $script = $dir . '/' . $type . '.php';

        if (!$dir || !is_readable($script)) {
            return;
        }

        $invoker = new Executable('php');
        $invoker->addArg('-f')
            ->addArg($script);

        foreach ($params as $key => $value) {
            $key = preg_replace('~[^a-z0-9]+~i', '_', trim($key));
            $invoker->setEnv('DP_' . strtoupper($key), $value);
        }

        $invoker->setEnv($env);
        $invoker->setEnv('DEPLOYMENT_APP_BASEDIR', $this->targetDirectory->getPathname());
        $invoker->setEnv('DEPLOYMENT_WEB_ROOT', $this->getWebRoot($params));

        if (!$invoker->execute(true)) {
            throw new exceptions\StageScriptException(sprintf('%s stage script failed', $type));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function install($params)
    {
        if (!$this->targetDirectory || !$this->targetDirectory->isDir()) {
            throw new exceptions\RuntimeException(sprintf('Invalid darget directory: "%s"', (string)$this->targetDirectory));
        }

        $this->assertArchive();
        $this->archive->extractTo($this->targetDirectory);
        $this->triggerDeployScript(self::STAGE_INSTALL, $params);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($params)
    {
        $this->triggerDeployScript(self::STAGE_REMOVE, $params);
        return $this;
    }

    /**
     * @see \rampage\nexus\node\installer\StageSubscriberInterface::afterActivate()
     */
    public function afterActivate($params)
    {
        $this->triggerDeployScript(self::STAGE_POST_ACTIVATE, $params);
        return $this;
    }

    /**
     * @see \rampage\nexus\node\installer\StageSubscriberInterface::afterRollback()
     */
    public function afterRollback($params, $isRollbackTarget)
    {
        $this->triggerDeployScript(self::STAGE_POST_ROLLBACK, $params, [
            'DEPLOYMENT_ROLLBACK_TARGET' => $isRollbackTarget? 1 : 0
        ]);

        return $this;
    }

    /**
     * @see \rampage\nexus\node\installer\StageSubscriberInterface::beforeActivate()
     */
    public function beforeActivate($params)
    {
        $this->triggerDeployScript(self::STAGE_PRE_ACTIVATE, $params);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeRollback($params, $isRollbackTarget)
    {
        $this->triggerDeployScript(self::STAGE_PRE_ROLLBACK, $params, [
            'DEPLOYMENT_ROLLBACK_TARGET' => $isRollbackTarget? 1 : 0
        ]);

        return $this;
    }

    /**
     * @see \rampage\nexus\node\installer\StageSubscriberInterface::afterDeactivate()
     */
    public function afterDeactivate($params)
    {
        $this->triggerDeployScript(self::STAGE_PRE_DEACTIVATE, $params);
    }

    /**
     * @see \rampage\nexus\node\installer\StageSubscriberInterface::beforeDeactivate()
     */
    public function beforeDeactivate($params)
    {
        $this->triggerDeployScript(self::STAGE_POST_DEACTIVATE, $params);
    }
}
