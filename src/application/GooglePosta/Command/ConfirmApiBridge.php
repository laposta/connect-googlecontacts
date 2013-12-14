<?php

namespace GooglePosta\Command;

use Command\Abstraction\CommandInterface;
use Config\Config;

class ConfirmApiBridge implements CommandInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    private $authCode;

    /**
     * @var array
     */
    private $tokens;

    /**
     * @param Config $config
     */
    function __construct(Config $config)
    {
        $this->config = $config;
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

        $this->tokens = array(
            'access' => filter_var($tokens['access_token'], FILTER_SANITIZE_STRING),
            'refresh' => isset($tokens['refresh_token']) ? filter_var($tokens['refresh_token'], FILTER_SANITIZE_STRING) : '',
        );
    }
}
