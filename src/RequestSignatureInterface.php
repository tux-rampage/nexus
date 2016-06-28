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

namespace Rampage\Nexus;

use Psr\Http\Message\RequestInterface as HttpRequest;
use Zend\Http\Request as ClientHttpRequest;

/**
 * Sign an http request
 */
interface RequestSignatureInterface
{
    /**
     * Create a request signature
     *
     * @param HttpRequest  $request  The request to sign
     * @param array        $data     Additional request data to sign (Commonly used to add shared secrets)
     */
    public function sign(ClientHttpRequest $request, array $data = null);

    /**
     * Verify the request signature
     *
     * @param HttpRequest  $request  The request to verify
     * @param array        $data     Additional data (commonly used to provide shared secrets)
     * @return bool
     */
    public function verify(HttpRequest $request, array $data = null);
}