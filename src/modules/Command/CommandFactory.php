<?php

namespace Command;

use Command\Abstraction\CommandInterface;
use Command\Abstraction\FactoryInterface;

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
     * Default constructor
     *
     * @param DependManagerProxy $dependencyManager
     * @param CommandQueue       $queuePrototype
     */
    function __construct(DependManagerProxy $dependencyManager, CommandQueue $queuePrototype)
    {
        $this->dependencyManager = $dependencyManager;
        $this->queuePrototype    = $queuePrototype;
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
        return clone $this->dependencyManager->get($className);
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
