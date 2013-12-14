<?php

namespace Logger;

use Logger\Abstraction\AbstractLogger;

class SystemLogger extends AbstractLogger
{
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function log($level, $message, $context = array())
    {
        if (!$this->levelAccepted($level)) {
            return;
        }

        error_log(
            $this->getTimeString() . ' ' . trim($this->interpolate($message, $context))
        );
    }
}
