<?php

namespace Logger;

use Logger\Abstraction\AbstractLogger;
use Logger\Abstraction\LogLevel;
use Logger\Adapter\Abstraction\AdapterInterface;
use Logger\Adapter\System;
use Traversable;

class Logger extends AbstractLogger
{
    /**
     * @var int
     */
    private $logLevel;

    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var array
     */
    protected $levelMap = array(
        LogLevel::ANY       => -1,
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
     * @param mixed            $logLevel
     * @param AdapterInterface $adapter
     */
    function __construct($logLevel = LogLevel::ERROR, AdapterInterface $adapter = null)
    {
        $this->logLevel = $this->resolveLevel($logLevel);
        $this->adapter  = $adapter;
    }

    /**
     * Set the desired log level LogLevel::*
     *
     * @param mixed $logLevel
     */
    public function setLogLevel($logLevel)
    {
        $this->logLevel = $this->resolveLevel($logLevel);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed             $level
     * @param string            $message
     * @param array|Traversable $context
     *
     * @return void
     */
    public function log($level, $message, $context = array())
    {
        if (!($this->logLevel === LogLevel::ANY || $this->resolveLevel($level) <= $this->logLevel)) {
            return;
        }

        if (!($this->adapter instanceof AdapterInterface)) {
            $this->adapter = new System();
        }

        $this->adapter->send(
            $level,
            $this->getTimeString() . ' ' . trim($this->interpolate($message, $context))
        );
    }

    /**
     * @param AdapterInterface $adapter
     *
     * @return Logger
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Retrieve a time string for the current time in the format 24:59:59.9999
     *
     * @return string
     */
    private function getTimeString()
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
    private function interpolate($message, $context = array())
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
}
