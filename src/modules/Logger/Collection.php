<?php

namespace Logger;

use Logger\Abstraction\AbstractLogger;
use Logger\Abstraction\LoggerInterface;

class Collection extends AbstractLogger
{
    /**
     * @var \SplObjectStorage
     */
    private $collection;

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
        $this->collection->rewind();

        foreach ($this->collection as $logger) {
            /** @var $logger LoggerInterface */
            $logger->log($level, $message, $context);
        }
    }

    /**
     * Add a logger to the queue
     *
     * @param LoggerInterface $logger
     */
    public function add(LoggerInterface $logger)
    {
        $this->collection->attach($logger);
    }

    /**
     * @param LoggerInterface $logger
     */
    public function remove(LoggerInterface $logger)
    {
        $this->collection->detach($logger);
    }
}
