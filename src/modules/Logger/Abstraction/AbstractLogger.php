<?php

namespace Logger\Abstraction;

use Traversable;

abstract class AbstractLogger implements LoggerInterface
{
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
