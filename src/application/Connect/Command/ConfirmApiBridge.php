<?php

namespace Connect\Command;

use Command\Abstraction\AbstractCommand;
use Google_Client;

class ConfirmApiBridge extends AbstractCommand
{
    /**
     * @var string
     */
    private $authCode;

    /**
     * @var array
     */
    private $tokens;

    /**
     * @var Google_Client
     */
    private $client;

    /**
     * @param Google_Client $client
     */
    function __construct(Google_Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $authCode
     *
     * @return ConfirmApiBridge
     */
    public function setAuthCode($authCode)
    {
        $this->authCode = $authCode;

        return $this;
    }

    /**
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * Execute the command
     *
     * @return ConfirmApiBridge
     * @throws \RuntimeException
     */
    public function execute()
    {
        $client = $this->client;

        $tokens = json_decode(
            $client->authenticate($this->authCode),
            true
        );

        if (!isset($tokens['access_token'])) {
            throw new \RuntimeException('Unable to confirm link to google contacts. Please try again.');
        }

        $this->tokens = $tokens;
    }
}
