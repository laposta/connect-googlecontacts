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
     * @var string
     */
    private $instanceId;

    /**
     * @var array
     */
    private $tags = array();

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
        $this->logLevel   = $this->resolveLevel($logLevel);
        $this->adapter    = $adapter;
        $this->instanceId = base_convert(round(microtime(true) * 1000000), 10, 36);

        $this->addTag($this->instanceId);
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
        if ($this->logLevel !== LogLevel::ANY && $this->resolveLevel($level) > $this->logLevel) {
            return;
        }

        if (!($this->adapter instanceof AdapterInterface)) {
            $this->adapter = new System();
        }

        $tags = $this->resolveTags();

        $this->adapter->send(
            $level,
            $tags . ' ' . $this->getTimeString() . ' ' . trim($this->interpolate($message, $context))
        );
    }

    /**
     * Resolve the message tags.
     *
     * @return string
     */
    protected function resolveTags()
    {
        if (empty($this->tags)) {
            return '';
        }

        $tags = '';

        foreach ($this->tags as $name => $value) {
            $label = '';

            if (is_string($name)) {
                $label .= $name . ':';
            }

            $tags .= '[' . $label . $value . ']';
        }

        return $tags;
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

        return date('H:i:s.') . str_pad(round(($microTime - intval($microTime)) * 1000), 3, '0', STR_PAD_LEFT);
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
        if (isset($this->levelMap[(string) $logLevel])) {
            return $this->levelMap[(string) $logLevel];
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

    /**
     * Add a tag to be prepended to all log messages
     *
     * @param string $value Tag value
     * @param string $key   Optional name for the tag
     *
     * @return $this
     */
    public function addTag($value, $key = '')
    {
        if (!is_scalar($key) || !is_scalar($value)) {
            return $this;
        }

        if (is_string($key) && !empty($key)) {
            $this->tags[$key] = $value;
        }
        else {
            $this->tags[] = $value;
        }

        return $this;
    }

    /**
     * Remove a tag
     *
     * @param $key
     *
     * @return $this
     */
    public function removeTag($key)
    {
        if (!isset($this->tags[$key])) {
            return $this;
        }

        unset($this->tags[$key]);

        return $this;
    }
}
