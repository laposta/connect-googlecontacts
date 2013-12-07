<?php

namespace GooglePosta\Command;

use Command\Abstraction\CommandInterface;
use Config\Config;
use DataStore\DataStore;

class InitializeApiBridge implements CommandInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var DataStore
     */
    private $dataStore;

    /**
     * Execute the command
     *
     * @return CommandInterface
     */
    public function execute()
    {
        return $this;
    }
}
