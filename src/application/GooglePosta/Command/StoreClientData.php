<?php

namespace GooglePosta\Command;

use Command\Abstraction\CommandInterface;
use Config\Config;
use DataStore\Adapter\File;
use DataStore\DataStore;
use GooglePosta\Entity\ClientData;
use Security\Cryptograph;
use Web\Exception\RuntimeException;

class StoreClientData implements CommandInterface
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
     * @param \GooglePosta\Entity\ClientData $clientData
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
     * @throws \Web\Exception\RuntimeException
     */
    public function execute()
    {
        if (empty($this->clientToken)) {
            throw new RuntimeException('Unable to load client data. A client token is required.');
        }

        $this->dataStore->setContent(array(
            'email' => $this->clientData->getEmail(),
            'lapostaApiToken' => $this->encode($this->clientData->getLapostaApiToken()),
            'googleAccessToken' => $this->encode($this->clientData->getGoogleAccessToken()),
            'googleRefreshToken' => $this->encode($this->clientData->getGoogleRefreshToken()),
        ));

        $this->dataStore->persist(new File($this->config->get('path.data') . '/' . $this->clientToken . '.php'));

        return $this;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    protected function encode($value)
    {
        return base64_encode($this->crypto->encode($value));
    }
}
