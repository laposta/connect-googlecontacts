<?php

namespace Lock;

use InvalidArgumentException;
use Lock\Abstraction\LockableInterface;

class Lock implements LockableInterface
{
    /**
     * Chars to be filtered from strings used as file names.
     */
    const RESERVED_FILENAME_CHARS = '/\?%*o:|o"<>. ';

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
     * @var int
     */
    protected $autoExpireAfter = 300; // default 5 minutes

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
        $file = $this->lockDir . '/' . $this->resolveKey($name);

        if (!file_exists($file)) {
            return false;
        }

        if (filemtime($file) < time() - $this->autoExpireAfter) {
            unlink($file);

            return false;
        }

        return true;
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
        return preg_replace(
            array('/\s+/', '/['. preg_quote(self::RESERVED_FILENAME_CHARS, '/').']+/i'),
            array('-', ''),
            $name
        );
    }
}
