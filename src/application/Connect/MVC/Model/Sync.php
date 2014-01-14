<?php

namespace Connect\MVC\Model;

use Command\CommandFactory;
use Connect\Command\LoadClientData;
use Connect\Command\LoadClientMap;
use Connect\Command\PurgeClientMap;
use Connect\Command\StoreClientData;
use Connect\Command\StoreClientMap;
use Connect\Command\Sync\SyncFromGoogle;
use Connect\Command\Sync\SyncFromLaposta;
use Connect\Entity\ClientData;
use Connect\Entity\ListMap;
use Connect\MVC\Base\Model;
use Exception;
use Logger\Abstraction\LoggerInterface;
use RuntimeException;

class Sync extends Model
{
    /**
     * @var ClientData
     */
    private $clientData;

    /**
     * @var ListMap
     */
    private $clientMap;

    /**
     * @var string
     */
    private $clientToken;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(CommandFactory $commandFactory, LoggerInterface $logger)
    {
        parent::__construct($commandFactory);

        $this->logger = $logger;
    }

    /**
     * Import contacts from google.
     *
     * @param string $email    Laposta account email
     * @param string $apiToken Laposta API token
     *
     * @throws \RuntimeException
     * @throws \Exception
     * @return $this
     */
    public function importFromGoogle($email, $apiToken)
    {
        $this->clientToken = $this->createClientToken($email);

        $this->loadClientData();

        if ($this->clientData->lapostaApiToken !== $apiToken) {
            throw new RuntimeException('Token mismatch. You are not permitted to perform this action.');
        }

        $this->loadClientMap();

        try {
            /** @var $command SyncFromGoogle */
            $command = $this->getCommandFactory()->create('Connect\Command\Sync\SyncFromGoogle');
            $command->setClientData($this->clientData)->setListMap($this->clientMap)->execute();
        }
        catch (Exception $e) {
            $this->persist();

            throw $e;
        }

        $this->persist();

        return $this;
    }


    /**
     * Reset and remove all lists from Laposta.
     *
     * @param string $email    Laposta account email
     * @param string $apiToken Laposta API token
     *
     * @throws \RuntimeException
     * @throws \Exception
     * @return $this
     */
    public function resetLaposta($email, $apiToken)
    {
        $this->clientToken = $this->createClientToken($email);

        $this->loadClientData();

        if ($this->clientData->lapostaApiToken !== $apiToken) {
            throw new RuntimeException('Token mismatch. You are not permitted to perform this action.');
        }

        $this->loadClientMap();

        try {
            /** @var $command SyncFromGoogle */
            $command = $this->getCommandFactory()->create('Connect\Command\Sync\RemoveAllFromLaposta');
            $command->setClientData($this->clientData)->setListMap($this->clientMap)->execute();

            $this->persist();

            /** @var $command PurgeClientMap */
            $command = $this->getCommandFactory()->create('Connect\Command\PurgeClientMap');
            $command->setClientToken($this->clientToken)->execute();
        }
        catch (Exception $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * Consume the given events to update contacts in google.
     *
     * @param string $clientToken
     * @param string $eventsJson
     *
     * @throws \RuntimeException
     * @throws \Exception
     * @return $this
     */
    public function consumeEvents($clientToken, $eventsJson)
    {
        $this->clientToken = filter_var($clientToken, FILTER_SANITIZE_STRING);

        $this->logger->info("Consuming events for client '$this->clientToken'");
        $this->logger->debug($eventsJson);

//        $eventsJson = preg_replace("/,\s*}/", " }", $eventsJson) . ';';

        $decoded = json_decode($eventsJson, true);

        if ($decoded === false) {
            throw new RuntimeException("Events data could not be parsed. Input is not valid JSON.");
        }

        $this->loadClientData();

        if (!($this->clientData instanceof ClientData) || empty($this->clientData->email)) {
            throw new RuntimeException("Given client token '$clientToken' does not exist.");
        }

        $this->loadClientMap();

        try {
            /** @var $command SyncFromLaposta */
            $command = $this->getCommandFactory()->create('Connect\Command\Sync\SyncFromLaposta');
            $command->setClientData($this->clientData)->setListMap($this->clientMap)->setEventList($decoded)->execute();
        }
        catch (Exception $e) {
            $this->persist();

            throw $e;
        }

        $this->persist();

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
        $command = $this->getCommandFactory()->create('Connect\Command\LoadClientData');
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
        $command = $this->getCommandFactory()->create('Connect\Command\LoadClientMap');
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
        $command = $this->getCommandFactory()->create('Connect\Command\StoreClientData');
        $command->setClientToken($this->clientToken)->setClientData($this->clientData)->execute();

        /** @var $command StoreClientMap */
        $command = $this->getCommandFactory()->create('Connect\Command\StoreClientMap');
        $command->setClientToken($this->clientToken)->setMap($this->clientMap)->execute();

        return $this;
    }
}
