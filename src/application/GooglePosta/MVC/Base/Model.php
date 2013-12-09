<?php

namespace GooglePosta\MVC\Base;

use Command\CommandFactory;

class Model extends \MVC\Model
{
    /**
     * @var CommandFactory
     */
    protected $commandFactory;

    /**
     * Constructor override.
     *
     * @param CommandFactory $commandFactory
     */
    public function __construct(CommandFactory $commandFactory)
    {
        parent::__construct();

        $this->commandFactory = $commandFactory;
    }

    /**
     * @inheritdoc
     */
    public function persist()
    {
    }
}
