<?php

namespace Connect\Command;

use Command\Abstraction\AbstractCommand;

class CreateClientToken extends AbstractCommand
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $clientToken;

    /**
     * @param $identifier
     *
     * @return CreateClientToken
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientToken()
    {
        return $this->clientToken;
    }

    /**
     * Execute the command
     *
     * @return CreateClientToken
     */
    public function execute()
    {
        $this->clientToken = sha1($this->identifier);
    }
}
