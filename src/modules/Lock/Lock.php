<?php

namespace Lock;

use InvalidArgumentException;

class Lock implements LockableInterface
{
    /**
     * @var string
     */
    protected $lockDir;

    /**
     * Default constructor
     *
     * @param string $lockDir
     *
     * @throws InvalidArgumentException
     */
    function __construct($lockDir)
    {
        if (empty($lockDir) || !is_dir($lockDir) || !is_readable($lockDir) || !is_writable($lockDir)) {
            throw new InvalidArgumentException("Designated lock directory '$lockDir' is not a readable and writable directory.");
        }

        $this->lockDir = rtrim($lockDir, '/');
    }

    /**
     * Create a named lock
     *
     * @param string $name
     *
     * @return bool
     */
    public function lock($name)
    {
        if ($this->isLocked($name)) {
            return false;
        }

        return touch($this->lockDir . '/' . $this->resolveKey($name));
    }

    /**
     * Test for state of a named lock
     *
     * @param string $name
     *
     * @return bool
     */
    public function isLocked($name)
    {
        return file_exists($this->lockDir . '/' . $this->resolveKey($name));
    }

    /**
     * Unlock a named lock
     *
     * @param string $name
     *
     * @return bool
     */
    public function unlock($name)
    {
        return unlink($this->lockDir . '/' . $this->resolveKey($name));
    }

    /**
     * Convert a lock name into a usable unique file name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function resolveKey($name)
    {
        return md5($name);
    }
}
