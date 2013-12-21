<?php

namespace GooglePosta\MVC\Model;

use GooglePosta\Command\LoadClientData;
use GooglePosta\Command\LoadClientMap;
use GooglePosta\Command\StoreClientData;
use GooglePosta\Command\StoreClientMap;
use GooglePosta\Command\Sync\SyncGoogle;
use GooglePosta\Entity\ClientData;
use GooglePosta\MVC\Base\Model;
use RuntimeException;

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
     * Import contacts from google.
     *
     * @param string $email    Laposta account email
     * @param string $apiToken Laposta API token
     *
     * @return $this
     * @throws \RuntimeException
     */
    public function importFromGoogle($email, $apiToken)
    {
        $this->clientToken = $this->createClientToken($email);

        $this->loadClientData();

        if ($this->clientData->lapostaApiToken !== $apiToken) {
            throw new RuntimeException('Token mismatch. You are not permitted to perform this action.');
        }

        /** @var $command SyncGoogle */
        $command = $this->getCommandFactory()->create('GooglePosta\Command\Sync\SyncGoogle');
        $command->setClientData($this->clientData)->execute();

        return $this;
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
        $command = $this->getCommandFactory()->create('GooglePosta\Command\LoadClientData');
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
        $command = $this->getCommandFactory()->create('GooglePosta\Command\LoadClientMap');
        $command->setClientToken($this->clientToken)->execute();

        $this->clientMap = $command->getMap();

        return $this;
    }

    /**
     * Persist changes to the model.
     *
     * @return Sync
     */
    protected function persist()
    {
        /** @var $command StoreClientData */
        $command = $this->getCommandFactory()->create('GooglePosta\Command\StoreClientData');
        $command->setClientToken($this->clientToken)->setClientData($this->clientData)->execute();

        /** @var $command StoreClientMap */
        $command = $this->getCommandFactory()->create('GooglePosta\Command\StoreClientMap');
        $command->setClientToken($this->clientToken)->setMap($this->clientMap)->execute();

        return $this;
    }
}
