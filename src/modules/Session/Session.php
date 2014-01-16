<?php

namespace Session;

use InvalidArgumentException;
use RuntimeException;

class Session
{
    /**
     * @var bool
     */
    private $started = false;

    /**
     * @var bool
     */
    private $closed = false;

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @param bool $autoStart
     */
    function __construct($autoStart = false)
    {
        if (!$autoStart) {
            return;
        }

        $this->start();
    }

    /**
     * Start the session
     *
     * @return Session
     */
    public function start()
    {
        if ($this->started) {
            return $this;
        }

        if (session_id() === '') {
            $this->started = session_start();
        }
        else {
            $this->started = true;
        }

        $this->extrapolate($this->data, $_SESSION);

        return $this;
    }

    /**
     * Close the session
     *
     * @return Session
     */
    public function close()
    {
        session_write_close();

        $this->closed = true;

        return $this;
    }

    /**
     * Destroy the session
     *
     * @return Session
     */
    public function destroy()
    {
        session_destroy();

        $this->started = false;

        return $this;
    }

    /**
     * Remove all data from the session while leaving the session in tact.
     *
     * @return Session
     */
    public function clear()
    {
        session_unset();

        $this->data = array();

        return $this;
    }

    /**
     * Extrapolate nested array values into dot-notation accessible configuration values.
     *
     * @param array  $array
     * @param mixed  $node
     * @param string $prefix
     *
     * @throws InvalidArgumentException
     */
    protected function extrapolate(&$array, &$node, $prefix = '')
    {
        if (!is_array($array)) {
            throw new InvalidArgumentException('Expected first parameter to Config::extrapolate() to be an array.');
        }

        if (!empty($prefix)) {
            $array[$prefix] = $node;
        }

        if (!is_array($node)) {
            return;
        }

        $prefix = ltrim("$prefix.", '.');

        foreach ($node as $key => &$value) {
            $this->extrapolate($array, $value, $prefix . $key);
        }
    }

    /**
     * Check for the existence of a configuration value by key.
     *
     * @param $path
     *
     * @return bool
     */
    public function has($path)
    {
        $this->start();

        if (!isset($this->data[$path])) {
            return false;
        }

        return true;
    }

    /**
     * @param string $path
     *
     * @throws RuntimeException
     * @return mixed
     */
    public function get($path = '')
    {
        $this->start();

        if (empty($path)) {
            return $this->data;
        }

        if (!isset($this->data[$path])) {
            return null;
        }

        return $this->data[$path];
    }

    /**
     * @param string $path
     * @param mixed  $value
     *
     * @return $this
     *
     * @throws \RuntimeException
     */
    public function set($path, $value)
    {
        if ($this->closed) {
            throw new RuntimeException('Unable to write to a closed session');
        }

        $this->start();

        $this->extrapolate($this->data, $value, $path);

        $pathParts = explode('.', $path);
        $target    = & $_SESSION;
        foreach ($pathParts as $key) {
            $target = & $target[$key];
        }
        $target = $value;

        return $this;
    }
} 
