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

namespace Rampage\Nexus\MongoDB\Repository\OAuth2;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Rampage\Nexus\Config\PropertyConfigInterface;
use Rampage\Nexus\Entities\OAuth2\UIClientEntity;

/**
 * Client repository
 */
final class ClientRepository implements ClientRepositoryInterface
{
    /**
     * @var PropertyConfigInterface
     */
    private $config;

    /**
     * @param PropertyConfigInterface $config
     */
    public function __construct(PropertyConfigInterface $config)
    {
        $this->config = $config;
    }

    private function ensureUiSecret($secret)
    {
        // FIXME: ui secret
        return $secret;
    }

    /**
     * {@inheritDoc}
     * @see \League\OAuth2\Server\Repositories\ClientRepositoryInterface::getClientEntity()
     */
    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true)
    {
        if ($clientIdentifier != UIClientEntity::ID) {
            return null;
        }

        $secret = $this->ensureUiSecret($this->config->get('ui.secret'));
        $client = new UIClientEntity();



        // TODO Auto-generated method stub

    }


}
