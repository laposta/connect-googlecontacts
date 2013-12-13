<?php

namespace GooglePosta\MVC\Model;

use Command\CommandFactory;
use GooglePosta\Command\ConfirmApiBridge;
use GooglePosta\Command\InitializeApiBridge;
use GooglePosta\Command\LoadClientData;
use GooglePosta\Command\PurgeClientData;
use GooglePosta\Command\StoreClientData;
use GooglePosta\Entity\ClientData;
use GooglePosta\MVC\Base\Model;
use Session\Session;

class Sync extends Model
{
    /**
     * @var string
     */
    public $clientToken;

    /**
     * @var \GooglePosta\Entity\ClientData
     */
    public $clientData;

    /**
     * @return Authority
     */
    public function loadClientData()
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
     * Persist changes to the model.
     *
     * @return Sync
     */
    public function persist()
    {
        $this->storeClientData();

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
}
