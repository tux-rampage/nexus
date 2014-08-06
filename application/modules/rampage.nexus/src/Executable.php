<?php
/**
 * Copyright (c) 2014 Axel Helmert
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
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus;

use RuntimeException;
use BadMethodCallException;

class Executable
{
    /**
     * @var resource
     */
    protected $process = null;

    /**
     * @var int
     */
    protected $returnCode = null;

    /**
     * @var string
     */
    protected $command = null;

    /**
     * @var string[]
     */
    protected $env = array();

    /**
     * @var string
     */
    protected $cwd = null;

    /**
     * @var string[]
     */
    protected $args = array();

    /**
     * @var array
     */
    protected $pipes = array();

    /**
     * @var resource
     */
    protected $output = null;

    /**
     * @param string $command
     * @param array $args
     * @param string $keepEnvironment
     */
    public function __construct($command, array $args = array(), $keepEnvironment = true)
    {
        $this->command = $command;
        $this->setArgs($args);

        if ($keepEnvironment) {
            $this->setEnv($_ENV);
        }
    }

    public function __destruct()
    {
        if (is_resource($this->output)) {
            fclose($this->output);
        }
    }

    /**
     * @param string $cwd
     * @return self
     */
    public function setCwd($cwd)
    {
        $this->cwd = (string)$cwd;
        return $this;
    }

    /**
     * @return self
     */
    public function clearEnv()
    {
        $this->env = array();
        return $this;
    }

    /**
     * @param array|string|Traversable $env
     * @param string $value
     * @return self
     */
    public function setEnv($env, $value = null)
    {
        if (is_array($env) || ($env instanceof \Traversable)) {
            foreach ($env as $key => $value) {
                $this->setEnv($key, $value);
            }

            return $this;
        }

        $this->env[$env] = (string)$value;
        return $this;
    }

    /**
     * @param array $args
     * @return self
     */
    public function setArgs(array $args)
    {
        $this->args = array();

        foreach ($args as $arg) {
            $this->addArg($arg);
        }

        return $this;
    }

    /**
     * @param string $arg
     * @return self
     */
    public function addArg($arg)
    {
        $this->args[] = (string)$arg;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getReturnCode($wait = false)
    {
        if (!$wait || ($this->returnCode !== null)) {
            return $this->returnCode;
        }

        return $this->close();
    }

    /**
     * @return array
     */
    protected function getDescriptorSpec()
    {
        if ($this->output === null) {
            $this->output = fopen('php://temp', 'w+');
        }

        return array(
            0 => array('pipe', 'r'),
            1 => $this->output,
            2 => $this->output
        );
    }

    /**
     * @return string
     */
    protected function processInput()
    {
        $this->closePipe(0);
    }

    /**
     * @param int $index
     * @return self
     */
    protected function closePipe($index)
    {
        if (!isset($this->pipes[$index]) || ($this->pipes[$index] === null)) {
            continue;
        }

        @fclose($this->pipes[$index]);
        $this->pipes[$index] = null;

        return $this;
    }

    /**
     * @throws BadMethodCallException
     */
    protected function close()
    {
        if ($this->process === null) {
            throw new BadMethodCallException('No process to get a return value from');
        }

        foreach (array_keys($this->pipes) as $index) {
            $this->closePipe($index);
        }

        $this->returnCode = proc_close($this->process);
        $this->process = null;
        $this->pipes = array();

        return $this->returnCode;
    }

    /**
     * @return NULL|string
     */
    public function getOutput()
    {
        if (!is_resource($this->output)) {
            return null;
        }

        return file_get_contents($this->output);
    }

    /**
     * @param bool $wait Wait for the process to complete
     * @return bool
     */
    public function execute($wait = true)
    {
        $cwd = ($this->cwd == '')? getcwd() : $this->cwd;
        $command = array_filter($this->args, 'escapeshellarg');
        array_unshift($command, $this->command);

        $command = implode(' ', $command);
        $this->process = proc_open($command, $this->getDescriptorSpec(), $this->pipes, $cwd, $this->env);

        if ($this->process === false) {
            $this->process = null;
            return false;
        }

        $this->processInput();

        if (!$wait) {
            return true;
        }

        return ($this->close() === 0);
    }
}
