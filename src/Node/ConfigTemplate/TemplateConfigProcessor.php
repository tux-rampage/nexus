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

namespace Rampage\Nexus\Node\ConfigTemplate;

use Rampage\Nexus\Exception;
use Zend\Stdlib\ErrorHandler;

/**
 * Implements a simple template processor
 *
 * This will simply replace variables in the format `${varname}` in the template string.
 * and write the result to the target file
 */
class TemplateProcessor
{
    /**
     * @var string
     */
    protected $template;

    /**
     * @var string
     */
    protected $targetPath;

    /**
     * Creates the template processor
     *
     * @param   string  $template   The template content
     * @param   string  $targetPath The path to the resulting file
     */
    public function __construct($template, $targetPath)
    {
        $this->template = $template;
        $this->targetPath = $targetPath;
    }

    /**
     * Process the template and save the resulting config to the target path
     *
     * @param array|\ArrayAccess $options
     */
    public function process($options)
    {
        if (!is_array($options) && !($options instanceof \ArrayAccess)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Processing options must be an array or implement ArrayAccess, [%s] given.',
                is_object($options)? get_class($options) : gettype($options)
            ));
        }

        if (!$this->targetPath) {
            throw new Exception\LogicException('No config target path specified.');
        }

        $replaced = [];
        $placeholders = [];
        $result = $this->template;

        if (preg_match_all('~\$\{(?P<keys>[a-z_][a-z0-9_]*)\}~i', $this->template, $placeholders, PREG_PATTERN_ORDER)) {
            foreach ($placeholders['keys'] as $offset => $key) {
                if (in_array($key, $replaced)) {
                    continue;
                }

                $value = (isset($options[$key]))? $options[$key] : '';
                $placeholder = $placeholders[0][$offset];
                $result = str_replace($placeholder, $value, $result);
                $replaced[] = $key;
            }
        }

        ErrorHandler::start(E_ALL);
        $written = file_put_contents($this->targetPath, $result);
        ErrorHandler::stop();

        if (false === $written) {
            throw new Exception\RuntimeException(sprintf('Failed to write config to "%s"', $this->targetPath));
        }
    }
}
