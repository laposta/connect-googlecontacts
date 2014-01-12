<?php

namespace GooglePosta\MVC\Base;

use Command\CommandFactory;
use GooglePosta\Command\CreateClientToken;
use RuntimeException;

class Model extends \MVC\Model
{
    /**
     * @var CommandFactory
     */
    private $commandFactory;

    /**
     * Constructor override.
     *
     * @param CommandFactory $commandFactory
     */
    public function __construct(CommandFactory $commandFactory)
    {
        $this->commandFactory = $commandFactory;
    }

    /**
     * @param string $email
     *
     * @return string
     */
    protected function createClientToken($email)
    {
        /** @var $command CreateClientToken */
        $command = $this->getCommandFactory()->create('Connect\Command\CreateClientToken');
        $command->setIdentifier($email)->execute();

        return $command->getClientToken();
    }

    /**
     * Validate an email address.
     *
     * @param string $email
     *
     * @return $this
     * @throws \RuntimeException
     */
    protected function validateEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException("Expected a valid email address. Given '$email' is not valid.");
        }

        return $this;
    }

    /**
     * @param string $url
     *
     * @return $this
     * @throws \RuntimeException
     */
    protected function validateUrl($url)
    {
        if (filter_var($url, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException("Expected a valid URL. Given '$url' is not valid.");
        }

        return $this;
    }

    /**
     * Get the command factory
     *
     * @return \Command\CommandFactory
     */
    protected function getCommandFactory()
    {
        return $this->commandFactory;
    }
}
