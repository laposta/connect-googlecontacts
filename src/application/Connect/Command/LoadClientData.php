<?php

namespace GooglePosta\Command;

use Command\Abstraction\AbstractCommand;
use Command\Abstraction\CommandInterface;
use Config\Config;
use DataStore\Adapter\File;
use DataStore\DataStore;
use GooglePosta\Entity\ClientData;
use RuntimeException;
use Security\Cryptograph;

class LoadClientData extends AbstractCommand
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
        $this->clientData = new ClientData();
    }

    /**
     * @return ClientData
     */
    public function getClientData()
    {
        return $this->clientData;
    }

    /**
     * @param string $clientToken
     *
     * @return LoadClientData
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
     * @return CommandInterface
     *
     * @throws \RuntimeException
     */
    public function execute()
    {
        if (empty($this->clientToken)) {
            throw new RuntimeException('Unable to load client data. A client token is required.');
        }

        $this->dataStore->retrieve(new File($this->config->get('path.data') . '/' . $this->clientToken . '.php'));

        if ($this->dataStore->hasContent()) {
            $this->clientData->fromArray($this->dataStore->getContent());
        }

        $this->clientData->decode($this->crypto);

        return $this;
    }
}
