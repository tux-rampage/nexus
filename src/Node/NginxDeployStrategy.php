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

use Rampage\Nexus\Entities\ApplicationInstance;
use Rampage\Nexus\Entities\VHost;

use Rampage\Nexus\Executable;
use Rampage\Nexus\Exception;
use Rampage\Nexus\FileSystemInterface;
use Rampage\Nexus\FileSystem;


/**
 * Implements the deploy strategy for nginx
 */
class NginxDeployStrategy extends AbstractDeployStrategy implements DeployStrategyInterface, VHostDeployStrategyInterface
{
    const TYPE = 'nginx';

    const TEMPLATE_SERVER = 'server';
    const TEMPLATE_LOCATION = 'location';
    const TEMPLATE_ROOT_LOCATION = 'root-location';
    const TEMPLATE_MAINTENANCE = 'maintenance';
    const TEMPLATE_GLOBAL = 'global';

    const DIR_LOCATIONS = 'locations.d';
    const DIR_GLOBAL = 'global.d';

    /**
     * @var ConfigTemplate\TemplateLocatorInterface
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
    public function __construct(ConfigTemplate\TemplateLocatorInterface $templateLocator, FileSystemInterface $filesystem = null)
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
     * Returns the target path to the application instance
     *
     * @param   ApplicationInstance $application    The application instance to get the path for
     * @return  string                              The target path
     */
    protected function getApplicationPath(ApplicationInstance $application)
    {
        $path = sprintf('%s/versions/%s/%s', $this->applicationsPath, $application->getId(), $application->getPackage()->getId());
        return $path;
    }

    /**
     * Returns the application symlink
     *
     * @param   ApplicationInstance $instance   The application instance to get the symlink for
     * @return  string                          The path to the symlink of the currently deployed version
     */
    protected function getApplicationSymlinkPath(ApplicationInstance $instance)
    {
        $path = sprintf('%s/apps/%s', $this->applicationsPath, $instance->getId());
        return $path;
    }

    /**
     * Returns the path to the vhost config
     *
     * @param VHost $vhost
     * @return string
     */
    protected function getVHostConfigPath(VHost $vhost)
    {
        return sprintf('%s/sites.d/%s.conf', $this->configsPath, $vhost->getName());
    }

    /**
     * Returns the path to an instance specific config
     *
     * @param   ApplicationInstance $instance   The instance context
     * @param   string              $dir        The relative config directory
     * @return  string                          The path to the config file
     */
    protected function getConfigPath(ApplicationInstance $instance, $dir)
    {
        $vhost = $instance->getVHost();
        $path = sprintf(
            '%s/%s/%s/%s.conf',
            $this->configsPath,
            $vhost->isDefault()? '__default__' : $vhost->getName(),
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
            $template = ($instance->getPath() == '/')? self::TEMPLATE_ROOT_LOCATION : self::TEMPLATE_LOCATION;
        }

        $appPath = $this->getApplicationPath($instance);
        $docRoot = $appPath . '/' . trim((string)$this->installer->getWebRoot($instance->getUserParameters()), '/');
        $configPath = $this->getConfigPath($instance, self::DIR_LOCATIONS);
        $config = new ConfigTemplate\TemplateProcessor($this->templateLocator->getConfigTemplate(self::TYPE, $template, $instance->getFlavor()), $configPath);

        $this->filesystem->ensureDirectory(dirname($configPath), 0755);
        $config->process([
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
            throw new Exception\RuntimeException('Failed to remove existing symlink: ' . $path);
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
            throw new Exception\RuntimeException('Failed to remove existing symlink: ' . $path);
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
        $this->getInstaller($instance);

        $params = $instance->getUserParameters();
        $this->subscribers->beforeActivate($params);

        $this->createAndWriteConfig($instance);
        $this->updateSymlink($instance);
        $this->reloadService();

        $this->subscribers->afterActivate($params);
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
     * @see \rampage\nexus\node\DeployStrategyInterface::purge()
     */
    public function purge()
    {
        $this->filesystem->purgeDirectory($this->configsPath);
        $this->filesystem->purgeDirectory($this->applicationsPath);

        return $this;
    }

    /**
     * @see \rampage\nexus\node\DeployStrategyInterface::remove()
     */
    public function remove(ApplicationInstance $instance)
    {
        $path = $this->getApplicationPath($instance);
        $installer = $this->getInstaller($instance);

        $this->subscribers->beforeDeactivate($instance->getUserParameters());

        $this->removeConfig($instance);
        $this->removeSymlink($instance);
        $this->reloadService();

        $this->subscribers->afterDeactivate($instance->getUserParameters());

        $installer->remove($instance->getUserParameters());
        $this->filesystem->delete($path);

        return $this;
    }

    /**
     * @see \rampage\nexus\node\DeployStrategyInterface::rollback()
     */
    public function rollback(ApplicationInstance $instance)
    {
        if (!$instance->getPreviousPackage()) {
            throw new Exception\LogicException('Cannot rollback application without a previous state');
        }

        $path = $this->getApplicationPath($instance);
        $installer = $this->getInstaller($instance);

        $this->subscribers->beforeRollback($instance->getUserParameters());
        $this->destroyInstaller($installer);

        $instance->rollback();
        $installer = $this->getInstaller($instance);

        if (!is_dir($path)) {
            $this->filesystem->ensureDirectory($path, 0755);
            $installer->install($instance->getUserParameters());
        }

        $this->createAndWriteConfig($instance);
        $this->updateSymlink($instance);
        $this->reloadService();

        $this->subscribers->afterRollback($instance->getUserParameters(), true);
    }

    /**
     * @see \rampage\nexus\node\DeployStrategyInterface::stage()
     */
    public function stage(ApplicationInstance $instance)
    {
        $path = $this->getApplicationPath($instance);
        $installer = $this->getInstaller($instance);

        $this->filesystem->ensureDirectory($path, 0755);
        $installer->install($instance->getUserParameters());

        return $this;
    }

    /**
     * Returns the alias directives for the vhost
     *
     * @param   VHost   $vhost
     * @return  string
     */
    protected function getVHostAliasDirectives(VHost $vhost)
    {
        $directives = [];

        foreach ($vhost->getAliases() as $alias) {
            $directives[] = 'server_name ' . $alias . ';';
        }

        return implode("\n" . str_repeat(' ', 4), $directives);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Node\VHostDeployStrategyInterface::deployVHost()
     */
    public function deployVHost(VHost $vhost)
    {
        if ($vhost->isDefault()) {
            return $this;
        }

        $configPath = $this->getVHostConfigPath($vhost);
        $template = new ConfigTemplate\TemplateProcessor($this->templateLocator->getConfigTemplate(self::TYPE, self::TEMPLATE_SERVER, $vhost->getFlavor()), $configPath);
        $name = $vhost->getName();

        $params = [
            'global_configs' => sprintf('%s/%s/global.d/*.conf', $this->configsPath, $name),
            'location_configs' => sprintf('%s/%s/locations.d/*conf', $this->configsPath, $name),
            'servername' => $vhost->getName(),
            'server_aliases' => $this->getVHostAliasDirectives($vhost),
        ];

        $template->process($params);
        $this->reloadService();

        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Node\VHostDeployStrategyInterface::removeVHost()
     */
    public function removeVHost(VHost $vhost)
    {
        if ($vhost->isDefault()) {
            return $this;
        }

        $configFile = $this->getVHostConfigPath($vhost);
        $configDir  = sprintf('%s/%s', $this->configsPath, $vhost->getName());

        $this->filesystem->delete($configFile);
        $this->filesystem->delete($configDir);
        $this->reloadService();

        return $this;
    }
}
