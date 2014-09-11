<?php

namespace Connect\Command\ListBuilder;

use ApiHelper\Contacts\Entity\Collection\Groups;
use ApiHelper\Contacts\Laposta;
use Command\Abstraction\AbstractCommand;
use Command\Abstraction\CommandInterface;
use Config\Config;

class ListMailingListsForClient extends AbstractCommand
{
    /**
     * @var string
     */
    private $apiToken;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Laposta
     */
    private $laposta;

    /**
     * @var Groups
     */
    private $groups;

    /**
     * @param Laposta $laposta
     * @param Config  $config
     */
    function __construct(
        Laposta $laposta,
        Config $config
    ) {
        $this->config  = $config;
        $this->laposta = $laposta;
    }

    /**
     * @return Groups
     */
    public function getMailingLists()
    {
        return $this->groups;
    }

    /**
     * @param string $apiToken
     *
     * @return ListMailingListsForClient
     */
    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * Execute the command
     *
     * @return CommandInterface
     */
    public function execute()
    {
        $this->laposta->setAccessToken($this->apiToken);

        $this->groups = $this->laposta->getGroups();

        return $this;
    }
}
