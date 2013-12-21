<?php

namespace Command\Abstraction;

use Logger\Abstraction\LoggerAwareInterface;
use Logger\Abstraction\LoggerInterface;

abstract class AbstractCommand implements CommandInterface, LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
