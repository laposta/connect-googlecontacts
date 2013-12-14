<?php

namespace GooglePosta\MVC\Model;

use GooglePosta\Command\LoadClientData;
use GooglePosta\Command\LoadClientMap;
use GooglePosta\Command\StoreClientData;
use GooglePosta\Command\StoreClientMap;
use GooglePosta\Entity\ClientData;
use GooglePosta\MVC\Base\Model;

class Sync extends Model
{
    /**
     * @var \GooglePosta\Entity\ClientData
     */
    private $clientData;

    /**
     * @var array
     */
    private $clientMap;

    /**
     * @var string
     */
    private $clientToken;

    /**
     * @param \GooglePosta\Entity\ClientData $clientData
     *
     * @return Sync
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
        $this->loadClientData();

        return $this->clientData;
    }

    /**
     * @param array $clientMap
     *
     * @return Sync
     */
    public function setClientMap($clientMap)
    {
        $this->clientMap = $clientMap;

        return $this;
    }

    /**
     * @return array
     */
    public function getClientMap()
    {
        $this->loadClientMap();

        return $this->clientMap;
    }

    /**
     * @param string $clientToken
     *
     * @return Sync
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
     * @return Sync
     */
    protected function loadClientData()
    {
        if ($this->clientData instanceof ClientData || empty($this->clientToken)) {
            return $this;
        }

        /** @var $command LoadClientData */
        $command = $this->commandFactory->create('GooglePosta\Command\LoadClientData');
        $command->setClientToken($this->clientToken)->execute();

        $this->clientData = $command->getClientData();

        return $this;
    }

    /**
     * @return Sync
     */
    protected function loadClientMap()
    {
        if (!is_null($this->clientMap) || empty($this->clientToken)) {
            return $this;
        }

        /** @var $command LoadClientMap */
        $command = $this->commandFactory->create('GooglePosta\Command\LoadClientMap');
        $command->setClientToken($this->clientToken)->execute();

        $this->clientMap = $command->getMap();

        return $this;
    }

    /**
     * Persist changes to the model.
     *
     * @return Sync
     */
    public function persist()
    {
        $this->storeClientData();
        $this->storeClientMap();

        return $this;
    }

    /**
     * @return Sync
     */
    protected function storeClientData()
    {
        /** @var $command StoreClientData */
        $command = $this->commandFactory->create('GooglePosta\Command\StoreClientData');
        $command->setClientToken($this->clientToken)->setClientData($this->clientData)->execute();

        return $this;
    }

    /**
     * @return Sync
     */
    protected function storeClientMap()
    {
        /** @var $command StoreClientMap */
        $command = $this->commandFactory->create('GooglePosta\Command\StoreClientMap');
        $command->setClientToken($this->clientToken)->setMap($this->clientMap)->execute();

        return $this;
    }
}
