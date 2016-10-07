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

namespace Rampage\Nexus\Action;

use Rampage\Nexus\Config\PropertyConfigInterface;
use Rampage\Nexus\Exception\RuntimeException;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use GuzzleHttp\Cookie\SetCookie;

use Zend\Stratigility\MiddlewareInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;


/**
 * Renders the user interface page
 */
class UiAction implements MiddlewareInterface
{
    /**
     * @var TemplateRendererInterface
     */
    private $renderer;

    /**
     * @var PropertyConfigInterface
     */
    private $config;

    /**
     * @param TemplateRendererInterface $renderer
     */
    public function __construct(TemplateRendererInterface $renderer, PropertyConfigInterface $config)
    {
        $this->renderer = $renderer;
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stratigility\MiddlewareInterface::__invoke()
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $out = null)
    {
        $secret = $this->config->get('ui.secret');

        if (!$secret) {
            throw new RuntimeException('Missing UI client scret. Please specify ui.secret in your runtime configuration');
        }

        $cookie = new SetCookie();
        $cookie->setName('rnxUiClientSecret');
        $cookie->setValue($secret);
        $cookie->setPath('/');
        $cookie->setHttpOnly(false);
        $cookie->setDiscard(true);

        $response = new HtmlResponse($this->renderer->render('ui::index'));
        return $response->withAddedHeader('Set-Cookie', $cookie->__toString());
    }
}
