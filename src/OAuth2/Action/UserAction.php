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

namespace Rampage\Nexus\OAuth2\Action;

use Rampage\Nexus\Action\AbstractRestApi;
use Rampage\Nexus\OAuth2\Repository\UserRepositoryInterface;

/**
 * User API
 */
class UserAction extends AbstractRestApi
{
    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Action\AbstractRestApi::__construct()
     */
    public function __construct(UserRepositoryInterface $repo)
    {
        parent::__construct($repo);
    }
}