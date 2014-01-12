<?php

namespace Lock;

interface LockableInterface
{
    /**
     * Create a named lock
     *
     * @param string $name
     *
     * @return bool
     */
    public function lock($name);

    /**
     * Test for state of a named lock
     *
     * @param string $name
     *
     * @return bool
     */
    public function isLocked($name);

    /**
     * Unlock a named lock
     *
     * @param string $name
     *
     * @return bool
     */
    public function unlock($name);
} 
