<?php
/**
 * Copyright (c) 2016 Axel Helmert
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
 * @copyright Copyright (c) 2016 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\Config;

use Zend\Expressive\ConfigManager\ConfigManager;
use Zend\Expressive\ConfigManager\PhpFileProvider;
use ArrayObject;


/**
 * Config provider for deployment master apps
 */
class MasterConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string
     */
    protected $key = 'master';

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Config\ConfigProviderInterface::getConfig()
     */
    public function getConfig()
    {
        $configManager = new ConfigManager(
            [
                function() { return require __DIR__ . '/../../config/config.php'; },
                new PhpFileProvider(__DIR__ . '/../../config/' . $this->key . '.d/*.conf.php'),
                new PhpFileProvider(__DIR__ . '/../../config/conf.d/*.local.php'),
            ],
            __DIR__ . '/_generated.' . $this->key . '.config.php'
        );

        return new ArrayObject($configManager->getMergedConfig(), ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Config\ConfigProviderInterface::write()
     */
    public function write()
    {
        $filename = __DIR__ . '/../../config/_generated.' . $this->key . '.config.php';
        @unlink($filename);

        $config = $this->getConfig()->getArrayCopy();
        file_put_contents($filename, '<?php return ' . var_export($config, true) . ';');
    }

    /**
     * @return array
     */
    public function __invoke()
    {
        return $this->getConfig();
    }
}