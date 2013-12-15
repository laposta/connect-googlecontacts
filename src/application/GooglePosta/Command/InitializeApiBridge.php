<?php

namespace GooglePosta\Command;

use Command\Abstraction\AbstractCommand;
use Google_Client;

class InitializeApiBridge extends AbstractCommand
{
    /**
     * @var string
     */
    private $redirectUrl;

    /**
     * @var \Google_Client
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
