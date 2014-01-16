<?php

namespace Connect\Command;

use Command\Abstraction\AbstractCommand;
use Config\Config;
use DataStore\Adapter\File;
use DataStore\DataStore;
use Connect\Entity\ListMap;
use RuntimeException;

class StoreClientMap extends AbstractCommand
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
     * @param Config      $config
     * @param DataStore   $dataStore
     */
    function __construct(Config $config, DataStore $dataStore)
    {
        $this->config     = $config;
        $this->dataStore  = $dataStore;
    }

    /**
     * @param ListMap $map
     *
     * @return StoreClientMap
     */
    public function setMap($map)
    {
        $this->map = $map;

        return $this;
    }

    /**
     * @param string $clientToken
     *
     * @return StoreClientMap
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
     * @return StoreClientMap
     *
     * @throws \RuntimeException
     */
    public function execute()
    {
        if (empty($this->clientToken)) {
            throw new RuntimeException('Unable to load client data. A client token is required.');
        }

        if (is_null($this->map)) {
            return $this;
        }

        $this->dataStore->setContent($this->map->toArray());

        $this->dataStore->persist(
            new File($this->config->get('path.data') . '/maps/' . $this->clientToken . '.php')
        );

        return $this;
    }
}
