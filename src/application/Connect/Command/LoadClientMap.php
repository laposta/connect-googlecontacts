<?php

namespace Connect\Command;

use Command\Abstraction\AbstractCommand;
use Config\Config;
use DataStore\Adapter\File;
use DataStore\DataStore;
use Connect\Entity\ClientData;
use Connect\Entity\ListMap;
use RuntimeException;

class LoadClientMap extends AbstractCommand
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
     * @var ListMap
     */
    private $map;

    /**
     * @param Config    $config
     * @param DataStore $dataStore
     */
    function __construct(Config $config, DataStore $dataStore)
    {
        $this->config     = $config;
        $this->dataStore  = $dataStore;
        $this->map        = new ListMap();
    }

    /**
     * @return ListMap
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * @param string $clientToken
     *
     * @return LoadClientMap
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
     * @throws \RuntimeException
     */
    public function execute()
    {
        if (empty($this->clientToken)) {
            throw new RuntimeException('Unable to load client mappings. A client token is required.');
        }

        $this->map->clear();

        $this->dataStore->retrieve(
            new File($this->config->get('path.data') . '/maps/' . $this->clientToken . '.php')
        );

        if ($this->dataStore->hasContent()) {
            $this->map->fromArray($this->dataStore->getContent());
        }

        return $this;
    }
}
