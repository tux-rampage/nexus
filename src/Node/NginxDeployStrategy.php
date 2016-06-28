<?php
/**
 * Copyright (c) 2015 Axel Helmert
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
 * @copyright Copyright (c) 2015 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\Node;

/**
 * Implements the deploy strategy for nginx
 */
class NginxDeployStrategy extends AbstractDeployStrategy implements DeployStrategyInterface, VHostDeployStrategyInterface
{
    const TYPE = 'nginx';

    const TEMPLATE_SERVER = 'server';
    const TEMPLATE_LOCATION = 'location';
    const TEMPLATE_MAINTENANCE = 'maintenance';
    const TEMPLATE_GLOBAL = 'global';

    const DIR_LOCATIONS = 'locations.d';
    const DIR_GLOBAL = 'global.d';

    /**
     * @var config\TemplateLocatorInterface
     */
    protected $templateLocator;

    /**
     * @var string
     */
    protected $applicationsPath;

    /**
     * @var string
     */
    protected $configsPath;

    /**
     * @var FileSystem
     */
    protected $filesystem;

    /**
     * The executable to reload the nginx srevice
     *
     * @var Executable
     */
    protected $serviceExecutable = null;

    /**
     * @param config\TemplateLocatorInterface $templateLocator
     */
    public function __construct(config\TemplateLocatorInterface $templateLocator, FileSystem $filesystem = null)
    {
        $this->templateLocator = $templateLocator;
        $this->filesystem = $filesystem? : new FileSystem();
    }

    /**
     * @param string $applicationsPath
     */
    public function setApplicationsPath($applicationsPath)
    {
        $this->applicationsPath = $applicationsPath;
        return $this;
    }

    /**
     * @param string $configsPath
     */
    public function setConfigsPath($configsPath)
    {
        $this->configsPath = $configsPath;
        return $this;
    }

    /**
     * @param \rampage\nexus\Executable $serviceExecutable
     */
    public function setServiceExecutable(Executable $serviceExecutable)
    {
        $this->serviceExecutable = $serviceExecutable;
        return $this;
    }

    /**
     * @param ApplicationInstance $instance
     */
    protected function getApplicationPath(ApplicationInstance $instance)
    {
        $path = sprintf('%s/%s/%s', $this->applicationsPath, $instance->getId(), $instance->getPackage()->getId());
        return $path;
    }

    /**
     * @param ApplicationInstance $instance
     * @return string
     */
    protected function getApplicationSymlinkPath(ApplicationInstance $instance)
    {
        $path = sprintf('%s/%s/current', $this->applicationsPath, $instance->getId());
        return $path;
    }

    /**
     * @param VHost $vhost
     * @return string
     */
    protected function getVHostConfigPath(VHost $vhost)
    {
        return sprintf('%s/%s.conf', $this->configsPath, $vhost->getName());
    }

    /**
     * @param ApplicationInstance $instance
     * @param string $dir
     */
    protected function getConfigPath(ApplicationInstance $instance, $dir)
    {
        $path = sprintf(
            '%s/%s/%s/%s.conf',
            $this->configsPath,
            $instance->getVHost(),
            $dir,
            (string)$instance->getId()
        );

        return $path;
    }

    /**
     * Create the nginx location config
     *
     * @param ApplicationInstance $instance
     * @param string $template
     * @return self
     */
    protected function createLocationConfig(ApplicationInstance $instance, $template = null)
    {
        if (!$template) {
            $template = self::TEMPLATE_LOCATION;
        }

        $appPath = $this->getApplicationPath($instance);
        $docRoot = $appPath . '/' . trim((string)$this->installer->getWebRoot($instance->getUserParameters()), '/');
        $configPath = $this->getConfigPath($instance, self::DIR_LOCATIONS);
        $template = new config\TemplateConfigProcessor($this->templateLocator->getConfigTemplate(self::TYPE, $template, $instance->getFlavor()), $configPath);

        $this->filesystem->ensureDirectory(dirname($configPath), 0755);
        $template->process([
            'location' => $instance->getPath(),
            'document_root' => $docRoot
        ]);

        return $this;
    }

    /**
     * @param ApplicationInstance $instance
     */
    protected function createAndWriteConfig(ApplicationInstance $instance)
    {
        $this->createLocationConfig($instance);
        return $this;
    }

    /**
     * @param ApplicationInstance $instance
     */
    protected function createAndWriteMaintenanceConfig(ApplicationInstance $instance)
    {
        $this->createLocationConfig($instance, self::TEMPLATE_MAINTENANCE);
        return $this;
    }

    /**
     * Remove the config for the given application instance
     *
     * @param ApplicationInstance $instance
     */
    protected function removeConfig(ApplicationInstance $instance)
    {
        $configPaths = [
            $this->getConfigPath($instance, self::DIR_LOCATIONS),
            $this->getConfigPath($instance, self::DIR_GLOBAL)
        ];

        foreach ($configPaths as $configPath) {
            if (file_exists($configPath)) {
                $this->filesystem->delete($configPath);
            }
        }

        return $this;
    }

    /**
     * @param ApplicationInstance $instance
     * @return self
     */
    protected function updateSymlink(ApplicationInstance $instance)
    {
        $path = $this->getApplicationSymlinkPath($instance);

        if (file_exists($path) && !unlink($path)) {
            throw new RuntimeException('Failed to remove existing symlink: ' . $path);
        }

        symlink($this->getApplicationPath($instance), $path);

        return $this;
    }

    /**
     * @param ApplicationInstance $instance
     * @return self
     */
    protected function removeSymlink(ApplicationInstance $instance)
    {
        $path = $this->getApplicationSymlinkPath($instance);

        if (file_exists($path) && !unlink($path)) {
            throw new RuntimeException('Failed to remove existing symlink: ' . $path);
        }

        return $this;
    }

    /**
     * Relaod the nginx service
     *
     * @return self
     */
    protected function reloadService()
    {
        if ($this->serviceExecutable) {
            $this->serviceExecutable->execute(true);
        }

        return $this;
    }

    /**
     * @see \rampage\nexus\node\DeployStrategyInterface::activate()
     */
    public function activate(ApplicationInstance $instance)
    {
        $this->installer->setTargetDirectory($this->getApplicationPath($instance));
        $this->subscribers->beforeActivate($instance->getUserParameters());

        $this->createAndWriteConfig($instance);
        $this->updateSymlink($instance);
        $this->reloadService();

        $this->subscribers->afterActivate($instance->getUserParameters());
    }

    /**
     * @see \rampage\nexus\node\DeployStrategyInterface::prepareActivation()
     */
    public function prepareActivation(ApplicationInstance $instance)
    {
        $this->createAndWriteMaintenanceConfig($instance);
        $this->reloadService();

        return $this;
    }

    /**
     * @param string $dir
     * @return self
     */
    protected function purgeDirectory($dir)
    {
        $iterator = new \DirectoryIterator($dir);

        foreach ($iterator as $file) {
            if (in_array($file->getFilename(), ['.', '..'])) {
                continue;
            }

            $this->filesystem->delete($file->getPathname());
        }

        return $this;
    }

    /**
     * @see \rampage\nexus\node\DeployStrategyInterface::purge()
     */
    public function purge()
    {
        $this->purgeDirectory($this->configsPath)
            ->purgeDirectory($this->applicationsPath);

        return $this;
    }

    /**
     * @see \rampage\nexus\node\DeployStrategyInterface::remove()
     */
    public function remove(ApplicationInstance $instance)
    {
        $path = $this->getApplicationPath($instance);
        $this->installer->setTargetDirectory($path);

        $this->subscribers->beforeDeactivate($instance->getUserParameters());
        $this->removeConfig($instance);
        $this->removeSymlink($instance);
        $this->reloadService();
        $this->subscribers->afterDeactivate($instance->getUserParameters());

        $this->installer->remove($instance->getUserParameters());
        $this->filesystem->delete($path);

        return $this;
    }

    /**
     * @see \rampage\nexus\node\DeployStrategyInterface::rollback()
     */
    public function rollback(ApplicationInstance $toInstanceState)
    {
        $path = $this->getApplicationPath($toInstanceState);

        $this->installer->setTargetDirectory($path);
        $this->subscribers->beforeRollback($toInstanceState->getUserParameters(), true);

        if (!is_dir($path)) {
            $this->stage($toInstanceState);
        }

        $this->createAndWriteConfig($toInstanceState);
        $this->updateSymlink($toInstanceState);
        $this->reloadService();

        $this->subscribers->afterRollback($toInstanceState->getUserParameters(), true);
    }

    /**
     * @see \rampage\nexus\node\DeployStrategyInterface::stage()
     */
    public function stage(ApplicationInstance $instance)
    {
        $path = $this->getApplicationPath($instance);

        $this->filesystem->ensureDirectory($path, 0755);
        $this->installer->setTargetDirectory($path);
        $this->installer->install($instance->getUserParameters());

        return $this;
    }

    protected function getVHostAliasDirectives(VHost $vhost)
    {
        $directives = array();

        foreach ($vhost->getAliases() as $alias) {
            $directives[] = 'server_name ' . $alias . ';';
        }

        return implode("\n" . str_repeat(' ', 8), $directives);
    }

    /**
     * @see \rampage\nexus\node\VHostDeployStrategyInterface::deployVHost()
     */
    public function deployVHost(VHost $vhost)
    {
        $configPath = $this->getVHostConfigPath($vhost);
        $template = new config\TemplateConfigProcessor($this->templateLocator->getConfigTemplate(self::TYPE, self::TEMPLATE_SERVER, $vhost->getFlavor()), $configPath);

        $params = [
            'global_configs' => sprintf('%s/%s/global.d/*.conf', $this->configsPath, $vhost->getName()),
            'location_configs' => sprintf('%s/%s/locations.d/*conf', $this->configsPath, $vhost->getName()),
            'servername' => $vhost->getName(),
            'server_aliases' => $this->getVHostAliasDirectives($vhost),
            'ssl_cert_file' => $vhost->getSslCert(),
            'ssl_key_file' => $vhost->getSslKey(),
            'ssl_chain_file' => $vhost->getSslChain(),
            'ssl_chain_directive' => '',
        ];

        if ($vhost->getSslChain()) {
            $params['ssl_chain_directive'] = 'include "' . $vhost->getSslChain() . '";';
        }

        $template->process($params);
        $this->reloadService();

        return $this;
    }

    /**
     * @see \rampage\nexus\node\VHostDeployStrategyInterface::removeVHost()
     */
    public function removeVHost(VHost $vhost)
    {
        $configFile = $this->getVHostConfigPath($vhost);
        $configDir  = sprintf('%s/%s', $this->configsPath, $vhost->getName());

        $this->filesystem->delete($configFile);
        $this->filesystem->delete($configDir);
        $this->reloadService();

        return $this;
    }
}
