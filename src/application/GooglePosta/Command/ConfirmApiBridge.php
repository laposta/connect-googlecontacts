<?php

namespace GooglePosta\Command;

use Command\Abstraction\CommandInterface;
use Config\Config;
use GooglePosta\Entity\ClientData;

class ConfirmApiBridge implements CommandInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ClientData
     */
    private $clientData;

    /**
     * @var string
     */
    private $authCode;

    /**
     * @param Config $config
     */
    function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param \GooglePosta\Entity\ClientData $clientData
     *
     * @return ConfirmApiBridge
     */
    public function setClientData($clientData)
    {
        $this->clientData = $clientData;

        return $this;
    }

    /**
     * @return \GooglePosta\Entity\ClientData
     */
    public function getClientData()
    {
        return $this->clientData;
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
     * Execute the command
     *
     * @return CommandInterface
     *
     * @throws \RuntimeException
     */
    public function execute()
    {
        $client = new \Google_Client();
        $client->setClientId($this->config->get('google.client_id'));
        $client->setClientSecret($this->config->get('google.client_secret'));
        $client->setRedirectUri($this->config->get('google.return_url'));

        $tokens = json_decode(
            $client->authenticate($this->authCode),
            true
        );

        if (!isset($tokens['access_token'])) {
            throw new \RuntimeException('Unable to confirm link to google contacts. Please try again.');
        }

        $this->clientData->setGoogleAccessToken(filter_var($tokens['access_token'], FILTER_SANITIZE_STRING));

        if (!isset($tokens['refresh_token'])) {
            return;
        }

        $this->clientData->setGoogleRefreshToken(filter_var($tokens['refresh_token'], FILTER_SANITIZE_STRING));
    }
}
