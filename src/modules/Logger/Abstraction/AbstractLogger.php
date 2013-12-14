<?php

namespace Logger\Abstraction;

use Traversable;

abstract class AbstractLogger implements LoggerInterface
{
    /**
     * @var int
     */
    protected $level = -1;

    /**
     * @var array
     */
    protected $levelMap = array(
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT     => 1,
        LogLevel::CRITICAL  => 2,
        LogLevel::ERROR     => 3,
        LogLevel::WARNING   => 4,
        LogLevel::NOTICE    => 5,
        LogLevel::INFO      => 6,
        LogLevel::DEBUG     => 7,
    );

    /**
     * Default constructor
     *
     * @param mixed $logLevel
     */
    function __construct($logLevel)
    {
        $this->level = $this->resolveLevel($logLevel);
    }

    /**
     * Test if given log level is within configured log level range
     *
     * @param mixed $logLevel
     *
     * @return bool
     */
    public function levelAccepted($logLevel)
    {
        return $this->level === -1 || $this->resolveLevel($logLevel) <= $this->level;
    }

    /**
     * Set the desired log level
     *
     * @param mixed $logLevel
     */
    public function setLogLevel($logLevel)
    {
        $this->level = $this->resolveLevel($logLevel);
    }

    /**
     * Reset log level to include all arbitrary levels.
     */
    public function resetLogLevel()
    {
        $this->level = -1;
    }

    /**
     * Retrieve a time string for the current time in the format 24:59:59.9999
     *
     * @return string
     */
    protected function getTimeString()
    {
        $microTime = microtime(true);

        return date('H:i:s.') . round($microTime - intval($microTime) * 1000);
    }

    /**
     * Resolve the numeric level for a given log level identifier
     *
     * @param mixed $logLevel
     *
     * @return mixed
     */
    private function resolveLevel($logLevel)
    {
        if (intval($logLevel) != $logLevel && isset($this->levelMap[(string) $logLevel])) {
            return $this->levelMap[$logLevel];
        }

        return $logLevel;
    }

    /**
     * @param string            $message
     * @param array|Traversable $context
     *
     * @return string
     */
    protected function interpolate($message, $context = array())
    {
        if (empty($context) || !is_traversable($context)) {
            return $message;
        }

        $replace = array();

        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }

        return strtr($message, $replace);
    }

    /**
     * System is unusable.
     *
     * @param string            $message
     * @param array|Traversable $context
     *
     * @return null
     */
    public function emergency($message, $context = array())
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string            $message
     * @param array|Traversable $context
     *
     * @return null
     */
    public function alert($message, $context = array())
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string            $message
     * @param array|Traversable $context
     *
     * @return null
     */
    public function critical($message, $context = array())
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string            $message
     * @param array|Traversable $context
     *
     * @return null
     */
    public function error($message, $context = array())
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string            $message
     * @param array|Traversable $context
     *
     * @return null
     */
    public function warning($message, $context = array())
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string            $message
     * @param array|Traversable $context
     *
     * @return null
     */
    public function notice($message, $context = array())
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     * Example: User logs in, SQL logs.
     *
     * @param string            $message
     * @param array|Traversable $context
     *
     * @return null
     */
    public function info($message, $context = array())
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string            $message
     * @param array|Traversable $context
     *
     * @return null
     */
    public function debug($message, $context = array())
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
} 
