<?php

namespace Logger\Abstraction;

use Traversable;

/**
 * Interface LoggerInterface - Copy of PSR-3 Psr\Log\LoggerInterface with array type hints removed
 * to allow for traversable objects as context.
 *
 * @package Logger\Abstraction
 */
interface LoggerInterface
{
    /**
     * System is unusable.
     *
     * @param string            $message
     * @param array|Traversable $context
     *
     * @return null
     */
    public function emergency($message, $context = array());

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
    public function alert($message, $context = array());

    /**
     * Critical conditions.
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string            $message
     * @param array|Traversable $context
     *
     * @return null
     */
    public function critical($message, $context = array());

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string            $message
     * @param array|Traversable $context
     *
     * @return null
     */
    public function error($message, $context = array());

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
    public function warning($message, $context = array());

    /**
     * Normal but significant events.
     *
     * @param string            $message
     * @param array|Traversable $context
     *
     * @return null
     */
    public function notice($message, $context = array());

    /**
     * Interesting events.
     * Example: User logs in, SQL logs.
     *
     * @param string            $message
     * @param array|Traversable $context
     *
     * @return null
     */
    public function info($message, $context = array());

    /**
     * Detailed debug information.
     *
     * @param string            $message
     * @param array|Traversable $context
     *
     * @return null
     */
    public function debug($message, $context = array());

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed             $level
     * @param string            $message
     * @param array|Traversable $context
     *
     * @return null
     */
    public function log($level, $message, $context = array());
}
