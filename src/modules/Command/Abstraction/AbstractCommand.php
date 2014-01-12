<?php

namespace Command\Abstraction;

use Command\CommandFactory;
use Logger\Abstraction\LoggerAwareInterface;
use Logger\Abstraction\LoggerInterface;

abstract class AbstractCommand implements CommandInterface, LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CommandFactory
     */
    protected $commandFactory;

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param CommandFactory $commandFactory
     *
     * @return AbstractCommand
     */
    public function setCommandFactory($commandFactory)
    {
        $this->commandFactory = $commandFactory;

        return $this;
    }
}
