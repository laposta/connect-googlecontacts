<?php

namespace Logger;

use Logger\Abstraction\AbstractLogger;

class FileLogger extends AbstractLogger
{
    /**
     * @var string
     */
    private $logDirPath;

    /**
     * Constructor override
     *
     * @param mixed  $logLevel
     * @param string $logDirPath
     */
    function __construct($logLevel, $logDirPath)
    {
        parent::__construct($logLevel);

        $this->ratifyDirPath($logDirPath);

        $this->logDirPath = rtrim($logDirPath, '/');
    }

    /**
     * Ensure the log dir is valid and if not, attempt to create it.
     *
     * @param string $logDirPath
     *
     * @throws \RuntimeException
     */
    private function ratifyDirPath($logDirPath)
    {
        if (file_exists($logDirPath) && is_dir($logDirPath)) {
            return;
        }

        $created = mkdir($logDirPath, 0755, true);

        if (!$created) {
            throw new \RuntimeException("Unable to create log dir '$logDirPath'");
        }
    }

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
            $this->getTimeString() . ' ' . trim($this->interpolate($message, $context)) . "\n",
            3,
            $this->logDirPath . '/' . $level . '.' . date('Ymd') . '.log'
        );
    }
}
