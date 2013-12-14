<?php

namespace GooglePosta\Command;

use Command\Abstraction\CommandInterface;
use Config\Config;
use DataStore\Adapter\File;
use DataStore\DataStore;
use GooglePosta\Entity\ClientData;
use RuntimeException;

class LoadClientMap implements CommandInterface
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
     * @var array
     */
    private $map = array(
        'lists' => array(),
        'fields' => array(),
        'contacts' => array(),
    );

    /**
     * @param Config      $config
     * @param DataStore   $dataStore
     */
    function __construct(Config $config, DataStore $dataStore)
    {
        $this->config     = $config;
        $this->dataStore  = $dataStore;
        $this->clientData = new ClientData();
    }

    /**
     * @return array
     */
    public function getMap()
    {
        return $this->map;
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
     * @return LoadClientMap
     *
     * @throws \RuntimeException
     */
    public function execute()
    {
        if (empty($this->clientToken)) {
            throw new RuntimeException('Unable to load client mappings. A client token is required.');
        }

        $this->dataStore->retrieve(
            new File($this->config->get('path.data') . '/mappings/' . $this->clientToken . '.php')
        );
        $this->map = $this->dataStore->hasContent() ? $this->dataStore->getContent() : array();

        return $this;
    }
}
