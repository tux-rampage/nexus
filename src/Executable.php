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

namespace Rampage\Nexus;


/**
 * Provides an object orient interface for executables
 *
 * This will utilize proc_open to allow non-blocking execution of shell
 * commands.
 *
 * You can use `setOutput()` to pipe the command's STDOUT into another file or stream:
 *
 * ```php
 * $iconv = new Executable('iconv', ['-f', 'ISO-8859-15', 'source.txt']);
 * $target = fopen('/tmp/target.txt', 'w');
 * $iconv->setOutput($target);
 * $iconv->execute();
 * fclose($target);
 * ```
 */
class Executable
{
    /**
     * Flag to close STDOUT on destruction
     */
    const CLOSE_STDOUT = 1;

    /**
     * Flag to close STDERR on destruction
     */
    const CLOSE_STDERR = 2;

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
     * @var resource
     */
    protected $stderr = null;

    /**
     * @var int
     */
    private $ioFlags = 0;

    /**
     * Creates the executable instance
     *
     * @param   string  $command            The shell command to executable
     * @param   array   $args               Additional arguments appended to the command
     * @param   string  $keepEnvironment    Flag to keep the current environment variables
     */
    public function __construct($command, array $args = array(), $keepEnvironment = true)
    {
        $this->command = $command;
        $this->setArgs($args);

        if ($keepEnvironment) {
            $this->setEnv($_ENV);
        }
    }

    /**
     * Destructor
     *
     * Will close open pipes and i/o streams.
     *
     * The resource to the process, if still running, will be lost.
     * The behavior depends on the os whether the process is terminated
     * or detatched.
     */
    public function __destruct()
    {
        $this->closeAllPipes();
        $this->pipes = [];

        if (($this->ioFlags & self::CLOSE_STDOUT) && is_resource($this->output)) {
            fclose($this->output);
            $this->output = null;
        }

        if (($this->ioFlags & self::CLOSE_STDERR) && is_resource($this->stderr)) {
            fclose($this->stderr);
            $this->stderr = null;
        }
    }

    /**
     * Sets the working directory for the command
     *
     * @param   string  $cwd    Path to the working directory
     * @return  self
     */
    public function setCwd($cwd)
    {
        $this->cwd = (string)$cwd;
        return $this;
    }

    /**
     * Removes all environment variables
     *
     * If the executable is constructed with `$keepEnvironment = true`,
     * __the inherited environment variables will be removed as well__.
     *
     * @return self
     */
    public function clearEnv()
    {
        $this->env = array();
        return $this;
    }

    /**
     * Sets one or more environment variables for the command
     *
     * @param   array|string|Traversable    $env    The envvar name or an array of variables
     * @param   string                      $value  The value to set. This is ignored when $env is an array or `Traversable`.
     * @return  self                                Provides a fluent interface
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
     * Set additional arguments
     *
     * These will be escaped and appended to the command
     *
     * @param   array   $args   The arguments to set
     * @return  self            Provides a fluent interface
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
     * Add a single argument to the argument list
     *
     * @see     setArgs()           Arguments setter
     * @param   string      $arg    The argument to add
     * @return  self                Provides a fluent interface
     */
    public function addArg($arg)
    {
        $this->args[] = (string)$arg;
        return $this;
    }

    /**
     * Returns the exit code of the command
     *
     * @return  int     The exit code. If the command was not yet executed,
     *                  this will return `null`.
     */
    public function getReturnCode($wait = false)
    {
        if (!$wait || ($this->returnCode !== null)) {
            return $this->returnCode;
        }

        return $this->close();
    }

    /**
     * Creates the descriptor spec for proc_open
     *
     * @return array
     */
    protected function getDescriptorSpec()
    {
        if ($this->output === null) {
            $this->setOutput('php://temp');
        }

        if ($this->stderr === null) {
            $this->setErrorOutput('php://temp');
        }

        return [
            0 => [ 'pipe', 'r' ],
            1 => $this->output,
            2 => $this->stderr
        ];
    }

    /**
     * Ensures the input is processed
     *
     * This will simply close the `STDIN` pipe.
     *
     * @return string
     */
    protected function processInput()
    {
        $this->closePipe(0);
    }

    /**
     * Closes all open pipes
     */
    protected function closeAllPipes()
    {
        foreach ($this->pipes as $index => $pipe) {
            if ($pipe === null) {
                continue;
            }

            @fclose($pipe);
            $this->pipes[$index] = null;
        }
    }

    /**
     * Closes a specific pipe
     *
     * @param   int     $index  The index of the pipe to close
     * @return  self
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
     * Closes the process communication
     *
     * this will call proc_close() and therefore wait for it to return.
     *
     * @throws  Exception\BadMethodCallException    When the command was not executed
     * @return  int                                 The exit code of the command
     */
    protected function close()
    {
        if ($this->process === null) {
            throw new Exception\BadMethodCallException('No process to get a return value from');
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
     * Sets the stream for `STDOUT`
     *
     * @param   resource|string $stream The output stream resource or filename.
     * @return  self
     * @throws  Exception\InvalidArgumentException  When the stream is not a valid stream resource or filename
     * @throws  Exception\LogicException            When the command was already executed and not yet closed
     */
    public function setOutput($stream)
    {
        if ($this->process !== null) {
            throw new Exception\LogicException('Cannot set the output stream when the command is executed');
        }

        if (is_string($stream)) {
            $this->ioFlags = $this->ioFlags | self::CLOSE_STDOUT;
            $stream = fopen($stream, 'w+', false);
        }

        if (!is_resource($stream)) {
            throw new Exception\InvalidArgumentException('Output must be a valid stream resource!');
        }

        $this->output = $stream;
        return $this;
    }

    /**
     * Returns the output form STDOUT as string
     *
     * @return  string  The command output written to `STDOUT`. If there is no Stream
     *                  for `STDOUT`, `null` is returned.
     */
    public function getOutput()
    {
        if (!is_resource($this->output)) {
            return null;
        }

        return file_get_contents($this->output);
    }

    /**
     * Returns the output from STDERR as string
     *
     * @return  string  The command output written to `STDERR`.
     *                  If there is no stream for `STDERR`, `null` is returned.
     */
    public function getErrorOutput()
    {
        if (!is_resource($this->stderr)) {
            return null;
        }

        return stream_get_contents($this->stderr);
    }

    /**
     * Set the error output stream
     *
     * @param   resource|string $stream The stream resource or filename to use for `STDERR`
     * @return  self
     * @throws  Exception\InvalidArgumentException  When the stream is not a valid stream resource or filename
     * @throws  Exception\LogicException            When the command was already executed and not yet closed
     */
    public function setErrorOutput($stream)
    {
        if ($this->process !== null) {
            throw new Exception\LogicException('Cannot set the output stream when the command is executed');
        }

        if (is_string($stream)) {
            $this->ioFlags = $this->ioFlags | self::CLOSE_STDERR;
            $stream = fopen($stream, 'w+');
        }

        if (!is_resource($stream)) {
            throw new Exception\InvalidArgumentException('Error output must be a stream resource or a valid file name');
        }

        $this->stderr = $stream;
        return $this;
    }

    /**
     * Run the command
     *
     * @param   bool    $wait   Flag to control if the method blocks until the called process exits
     * @return  bool            `true` if the executable completed with exit code `0`.
     *                          If `$wait` is `false`, this method will always return `true`.
     *
     * @throws  Exception\RuntimeException  When creating the process failes on system level
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
            throw new Exception\RuntimeException('Failed to start process for: ' . $command);
        }

        $this->processInput();

        if (!$wait) {
            return true;
        }

        return ($this->close() === 0);
    }
}
