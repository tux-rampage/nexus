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

namespace Rampage\Nexus;

use Zend\Crypt\Password\PasswordInterface;
use Zend\Crypt\Password\Bcrypt as BcryptPasswordStrategy;

return [
    'di' => [
        'preferences' => [
            Repository\ApplicationRepositoryInterface::class => MongoDB\Repository\ApplicationRepository::class,
            Repository\DeployTargetRepositoryInterface::class => MongoDB\Repository\DeployTargetRepository::class,
            Repository\NodeRepositoryInterface::class => MongoDB\Repository\NodeRepository::class,
            Repository\PackageRepositoryInterface::class => MongoDB\Repository\PackageRepository::class,

            FileSystemInterface::class => FileSystem::class,

            Package\Installer\InstallerProviderInterface::class => Package\Installer\InstallerManager::class,
            Archive\ArchiveLoaderInterface::class => Archive\ArchiveLoader::class,

            Api\RequestSignatureInterface::class => Api\PublicKeySignature::class,
            Api\SignRequestInterface::class => Api\PublicKeySignature::class,

            Deployment\NodeStrategyProviderInterface::class => Deployment\NodeStrategyProvider::class,

            PasswordInterface::class => BcryptPasswordStrategy::class,
            Config\PropertyConfigInterface::class => 'RuntimeConfig',
        ],

        'instances' => [
            'RuntimeConfig' => [ 'aliasOf' => Config\ArrayConfig::class ],
        ],
    ]
];
