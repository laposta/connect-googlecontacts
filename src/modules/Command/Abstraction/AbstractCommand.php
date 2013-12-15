<?php

namespace Command\Abstraction;

use Logger\Abstraction\LoggerAwareInterface;
use Logger\Abstraction\LoggerInterface;

abstract class AbstractCommand implements CommandInterface, LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $log;

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->log = $logger;
    }
}
