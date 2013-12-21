<?php

namespace Command;

use Command\Abstraction\CommandInterface;

/**
 * Class CommandQueue
 *
 * @package GooglePosta\Command
 *
 * @method CommandInterface offsetGet($index)
 * @method CommandInterface current()
 */
class CommandQueue extends \ArrayIterator implements CommandInterface
{
    /**
     * @inheritdoc
     */
    public function __construct(array $array = array(), $flags = 0)
    {
        /*
         * Overridden constructor to add array type hint so codeblanche/depend knows its expecting an array.
         */
        parent::__construct($array, $flags);
    }

    /**
     * Execute the command
     *
     * @return CommandInterface
     */
    public function execute()
    {
        for ($this->rewind(); $this->valid(); $this->next()) {
            $command = $this->current();

            if (!($command instanceof CommandInterface)) {
                continue;
            }

            $command->execute();
        }
    }
}
