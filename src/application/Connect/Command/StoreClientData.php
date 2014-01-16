<?php

namespace Connect\Command;

use Command\Abstraction\AbstractCommand;
use Config\Config;
use DataStore\Adapter\File;
use DataStore\DataStore;
use Connect\Entity\ClientData;
use RuntimeException;
use Security\Cryptograph;

class StoreClientData extends AbstractCommand
{
    /**
     * @var string
     */
    private $clientToken;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var DataStore
     */
    private $dataStore;

    /**
     * @var ClientData
     */
    private $clientData;

    /**
     * @var Cryptograph
     */
    private $crypto;

    /**
     * @param Config      $config
     * @param Cryptograph $crypto
     * @param DataStore   $dataStore
     */
    function __construct(Config $config, Cryptograph $crypto, DataStore $dataStore)
    {
        $this->config     = $config;
        $this->crypto     = $crypto;
        $this->dataStore  = $dataStore;
    }

    /**
     * @param \Connect\Entity\ClientData $clientData
     *
     * @return StoreClientData
     */
    public function setClientData($clientData)
    {
        $this->clientData = $clientData;

        return $this;
    }

    /**
     * @param string $clientToken
     *
     * @return StoreClientData
     */
    public function setClientToken($clientToken)
    {
        $this->clientToken = $clientToken;

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
     * @return StoreClientData
     *
     * @throws \RuntimeException
     */
    public function execute()
    {
        if (empty($this->clientToken)) {
            throw new RuntimeException('Unable to load client data. A client token is required.');
        }

        $this->clientData->encode($this->crypto);
        $this->dataStore->setContent($this->clientData->toArray());
        $this->dataStore->persist(new File($this->config->get('path.data') . '/' . $this->clientToken . '.php'));

        return $this;
    }
}
