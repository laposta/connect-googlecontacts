<?php

namespace Connect\MVC\Model;

use Command\CommandFactory;
use DirectoryIterator;
use Connect\Command\LoadClientData;
use Connect\Command\LoadClientMap;
use Connect\Command\StoreClientData;
use Connect\Command\StoreClientMap;
use Connect\Command\Sync\SyncFromGoogle;
use Connect\Entity\ClientData;
use Connect\Entity\ListMap;
use Connect\MVC\Base\Model;
use Exception\ExceptionList;
use Exception;
use Lock\Abstraction\LockableInterface;
use Logger\Abstraction\LoggerInterface;
use Logger\Logger;
use RegexIterator;
use RuntimeException;
use SplFileInfo;

class Cli extends Model
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
     * @var LoggerInterface;
     */
    private $logger;

    /**
     * @var LockableInterface
     */
    private $lock;

    public function __construct(CommandFactory $commandFactory, LoggerInterface $logger, LockableInterface $lock)
    {
        parent::__construct($commandFactory);

        $this->logger = $logger;
        $this->lock   = $lock;
    }

    /**
     * Import contacts from google.
     *
     * @param string $dataDir
     *
     * @throws \RuntimeException
     * @return $this
     */
    public function importFromGoogle($dataDir)
    {
        if (!file_exists($dataDir) || !is_dir($dataDir)) {
            throw new RuntimeException("Unable to load clients from '$dataDir'. Given path is not a directory.");
        }

        $this->logger->info("Scanning directory '$dataDir' for bridges.");

        $directory = new DirectoryIterator($dataDir);
        $list      = new RegexIterator($directory, '/\.php$/i');

        for ($list->rewind(); $list->valid(); $list->next()) {
            try {
                $this->resetClientProperties();

                /** @var $file SplFileInfo */
                $file              = $list->current();
                $this->clientToken = $file->getBasename('.php');

                $this->logger->info("Commencing import for bridge '$this->clientToken'");

                $this->loadClientData();
                $this->loadClientMap();

                $this->clientData->token = $this->clientToken;

                /** @var $command SyncFromGoogle */
                $command = $this->getCommandFactory()->create('Connect\Command\Sync\SyncFromGoogle');
                $command->setClientData($this->clientData)->setListMap($this->clientMap)->execute();

                $this->persist();
            }
            catch (Exception $e) {
                $this->logger->error("{$e->getMessage()} on line '{$e->getLine()}' of '{$e->getFile()}'");
            }
        }

        return $this;
    }

    protected function resetClientProperties()
    {
        $this->clientData  = null;
        $this->clientMap   = null;
        $this->clientToken = null;
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
