<?php

namespace GooglePosta\MVC\Base;

use Command\CommandFactory;
use GooglePosta\Command\CreateClientToken;

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

    /**
     * @param string $email
     *
     * @return string
     */
    public function createClientToken($email)
    {
        /** @var $command CreateClientToken */
        $command = $this->commandFactory->create('GooglePosta\Command\CreateClientToken');
        $command->setIdentifier($email)->execute();

        return $command->getClientToken();
    }
}
