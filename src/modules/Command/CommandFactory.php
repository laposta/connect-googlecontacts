<?php

namespace Command;

use Command\Abstraction\AbstractCommand;
use Command\Abstraction\CommandInterface;
use Command\Abstraction\FactoryInterface;
use Logger\Abstraction\LoggerAwareInterface;
use Logger\Abstraction\LoggerInterface;

class CommandFactory implements FactoryInterface
{
    /**
     * @var DependManagerProxy
     */
    private $dependencyManager;

    /**
     * @var CommandQueue
     */
    private $queuePrototype;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Default constructor
     *
     * @param DependManagerProxy $dependencyManager
     * @param CommandQueue       $queuePrototype
     * @param LoggerInterface    $logger
     */
    function __construct(DependManagerProxy $dependencyManager, CommandQueue $queuePrototype, LoggerInterface $logger)
    {
        $this->dependencyManager = $dependencyManager;
        $this->queuePrototype    = $queuePrototype;
        $this->logger            = $logger;
    }

    /**
     * Create a new instance of given command class.
     *
     * @param string $className
     *
     * @return CommandInterface
     */
    public function create($className)
    {
        /** @var $instance AbstractCommand */
        $instance = $this->dependencyManager->get($className);

        /** @var $clone AbstractCommand */
        $clone = clone $instance;

        if ($clone instanceof LoggerAwareInterface) {
            $clone->setLogger($this->logger);
        }

        $clone->setCommandFactory($this);

        return $clone;
    }

    /**
     * Create a new command queue
     *
     * @return CommandQueue
     */
    public function createQueue()
    {
        return clone $this->queuePrototype;
    }
}
