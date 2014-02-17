<?php

namespace Cli;

class CliHelper
{
    /**
     * @var array
     */
    protected $args;

    /**
     * @var int
     */
    protected $argCount;

    /**
     * Default constructor
     */
    function __construct()
    {
        if (!$this->isCli()) {
            return;
        }

        global $argc, $argv;

        $this->argCount = $argc;
        $this->args     = $argv;
    }

    /**
     * @return int
     */
    public function getArgCount()
    {
        return $this->argCount;
    }

    /**
     * Test if current process is using command line interface
     *
     * @return bool
     */
    public function isCli()
    {
        return strtolower(php_sapi_name()) === 'cli';
    }

    /**
     * Return the command for the current script.
     *
     * @return string
     */
    public function getCurrentCommand()
    {
        if (!$this->isCli()) {
            return '';
        }

        global $argv;

        return implode(' ', $argv);
    }

    /**
     * Count the number of processes running matching current script and parameters
     *
     * @return int
     */
    public function countProcesses()
    {
        $command = $this->getCurrentCommand();

        return $this->countProcessesByPattern($command . '$');
    }

    /**
     * Count the number of processes matching the given regex pattern.
     *
     * @param string $pattern
     *
     * @return int
     */
    public function countProcessesByPattern($pattern)
    {
        $pattern = escapeshellcmd($pattern);

        return `ps ax | grep -E "{$pattern}" | grep php | grep -v grep | wc -l`;
    }

    /**
     * Retrieve a cli parameter by index. Index '0' is always the script name.
     *
     * @param int $index
     *
     * @return string
     */
    public function getArg($index)
    {
        if (!$this->hasArg($index)) {
            return '';
        }

        return $this->args[$index];
    }

    /**
     * Test for existence of an argument at index.
     *
     * @param int $index
     *
     * @return string
     */
    public function hasArg($index)
    {
        return isset($this->args[$index]);
    }
}
