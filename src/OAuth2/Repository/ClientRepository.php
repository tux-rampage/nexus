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

namespace Rampage\Nexus\OAuth2\Repository;

use Rampage\Nexus\OAuth2\Entities\UIClient;
use Rampage\Nexus\Config\PropertyConfigInterface;
use Rampage\Nexus\Exception\RuntimeException;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;


/**
 * Client repository
 */
final class UIClientRepository implements ClientRepositoryInterface
{
    const GRANT_TYPE_PASSWORD = 'password';

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

    /**
     * @param unknown $secret
     * @throws RuntimeException
     * @return unknown
     */
    private function ensureUiSecret($secret)
    {
        if (!$secret) {
            throw new RuntimeException('No frontend app secret specified. Please specify a UI secret.');
        }

        return $secret;
    }

    /**
     * {@inheritDoc}
     * @see \League\OAuth2\Server\Repositories\ClientRepositoryInterface::getClientEntity()
     */
    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true)
    {
        if (($clientIdentifier != UIClient::ID) || ($grantType != self::GRANT_TYPE_PASSWORD)) {
            return null;
        }

        if ($mustValidateSecret) {
            $secret = $this->ensureUiSecret($this->config->get('ui.secret'));
            if ($secret != $clientSecret) {
                return null;
            }
        }

        return new UIClient();
    }
}
