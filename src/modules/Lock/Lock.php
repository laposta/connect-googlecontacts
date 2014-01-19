<?php

namespace Lock;

use InvalidArgumentException;
use Lock\Abstraction\LockableInterface;

class Lock implements LockableInterface
{
    /**
     * @var string
     */
    protected $lockDir;

    /**
     * @var float
     */
    protected $waitTime;

    /**
     * @var float
     */
    protected $waitInterval;

    /**
     * Default constructor
     *
     * @param string $lockDir
     * @param float  $waitTime     Wait time in seconds [default: 0]
     * @param float  $waitInterval Interval between tests while waiting in seconds [default: 0.1 seconds]
     *
     * @throws \InvalidArgumentException
     */
    function __construct($lockDir, $waitTime = 0.0, $waitInterval = 0.1)
    {
        if (empty($lockDir) || !is_dir($lockDir) || !is_readable($lockDir) || !is_writable($lockDir)) {
            throw new InvalidArgumentException("Designated lock directory '$lockDir' is not a readable and writable directory.");
        }

        $this->lockDir      = rtrim($lockDir, '/');
        $this->waitTime     = floatval($waitTime);
        $this->waitInterval = floatval($waitInterval);
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
            if ($this->waitTime === 0) {
                return false;
            }

            $timer = 0;

            while (true) {
                usleep($this->waitInterval * 1000000);

                if (!$this->isLocked($name)) {
                    break;
                }

                if ($timer > $this->waitTime) {
                    return false;
                }

                $timer += $this->waitInterval;
            }
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
