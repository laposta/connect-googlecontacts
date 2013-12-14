<?php

namespace GooglePosta\Command;

use Command\Abstraction\CommandInterface;
use Config\Config;
use Google_Client;

class InitializeApiBridge implements CommandInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    private $redirectUrl;

    /**
     * @var \Google_Client
     */
    private $client;

    /**
     * @param Config        $config
     * @param Google_Client $client
     */
    function __construct(Config $config, Google_Client $client)
    {
        $this->config = $config;
        $this->client = $client;
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * Execute the command
     *
     * @return InitializeApiBridge
     */
    public function execute()
    {
        $client = $this->client;

        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $client->setScopes(
            array(
                'https://www.google.com/m8/feeds',
            )
        );

        $this->redirectUrl = $client->createAuthUrl();
    }
}
