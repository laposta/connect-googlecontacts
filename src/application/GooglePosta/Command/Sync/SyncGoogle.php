<?php

namespace GooglePosta\Command\Sync;

use ApiAdapter\Contacts\Entity\Collection\Contacts;
use ApiAdapter\Contacts\Entity\Collection\Groups;
use ApiAdapter\Contacts\Google;
use ApiAdapter\Contacts\Laposta;
use Command\Abstraction\AbstractCommand;
use Command\Abstraction\CommandInterface;
use Config\Config;
use GooglePosta\Entity\ClientData;
use GooglePosta\Entity\ListMap;

class SyncGoogle extends AbstractCommand
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Google
     */
    private $google;

    /**
     * @var Laposta
     */
    private $laposta;

    /**
     * @var ListMap
     */
    private $listMap;

    /**
     * @var string
     */
    private $apiToken;

    /**
     * @var ClientData
     */
    private $clientData;

    /**
     * @param Google  $google
     * @param Laposta $laposta
     * @param Config  $config
     */
    function __construct(Google $google, Laposta $laposta, Config $config)
    {
        $this->config = $config;
        $this->google = $google;
        $this->laposta = $laposta;
    }

    /**
     * @param string $apiToken
     *
     * @return SyncGoogle
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
     * @param ListMap $listMap
     *
     * @return SyncGoogle
     */
    public function setListMap(ListMap $listMap)
    {
        $this->listMap = $listMap;

        return $this;
    }

    /**
     * @return ListMap
     */
    public function getListMap()
    {
        return $this->listMap;
    }

    /**
     * @param \GooglePosta\Entity\ClientData $clientData
     *
     * @return SyncGoogle
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

    protected function synchronizeGroups(Groups $groups)
    {
//        $groups->dump();
    }

    protected function synchronizeContacts(Contacts $contacts)
    {
//        $contacts->dump();
    }

    /**
     * Execute the command
     *
     * @return CommandInterface
     */
    public function execute()
    {
        $this->clientData->googleTokenSet->refresh_token = $this->clientData->googleRefreshToken;

        $this->clientData->googleTokenSet->fromArray(
            $this->google->setAccessToken($this->clientData->googleTokenSet)
        );

        while ($this->google->hasMoreGroups()) {
            $this->synchronizeGroups($this->google->getGroups());

            break; // TODO(mertenvg): remove break
        }

        while ($this->google->hasMoreContacts()) {
            $this->synchronizeContacts($this->google->getContacts());

            break; // TODO(mertenvg): remove break
        }
    }
}
