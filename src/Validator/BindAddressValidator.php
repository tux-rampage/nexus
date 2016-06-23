<?php
/**
 * This is part of rampage.php
 * Copyright (c) 2013 Axel Helmert
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
 * @category  library
 * @package   rampage.core
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\Validator;

use Zend\Validator\AbstractValidator;
use Zend\Validator\Ip as IpValidator;

/**
 * Bind address validation
 */
class BindAddressValidator extends AbstractValidator
{
    /**
     * Bind regex
     */
    const BIND_REGEX = '~^((?<ip>\d{1,3]}(\.\d{1,3]}){3}):)?(?<port>\d+)';

    /**
     * @var string[]
     */
    protected $messageTemplates = array(
        'invalid' => 'Invalid bind specification: "%value%"',
        'invalidPort' => 'Invalid port number: %value%'
    );

    /**
     * @var string
     */
    protected $socketRegex = '~^socket:/.+~i';

    /**
     * @see \Zend\Validator\ValidatorInterface::isValid()
     */
    public function isValid($value)
    {
        if ($this->socketRegex && preg_match($this->socketRegex, $value)) {
            return true;
        }

        $matches = array();
        if (!preg_match(self::BIND_REGEX, $value, $matches)) {
            return false;
        }

        $result = true;

        if (isset($matches['ip']) && ($matches['ip'] != '0.0.0.0')) {
            $ipValidator = new IpValidator();
            if (!$ipValidator->isValid($matches['ip'])) {
                $this->abstractOptions['messages'] = $ipValidator->getMessages();
                $result = false;
            }
        }

        $port = (int)$matches['port'];
        if ($port < 1) {
            $this->error('invalidPort', $port);
            $result = false;
        }

        return $result;
    }
}
