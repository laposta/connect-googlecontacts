<?php

namespace Connect\Command\Sync;

use ApiHelper\Contacts\Google;
use ApiHelper\Contacts\Laposta;
use Command\Abstraction\AbstractCommand;
use Command\Abstraction\CommandInterface;
use Config\Config;
use Connect\Entity\ClientData;
use Connect\Entity\ListMap;
use Iterator\Abstraction\FactoryInterface;
use Lock\Abstraction\LockableInterface;

class RemoveAllFromLaposta extends AbstractCommand
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Google
     */
    private $google;

    /**
     * @var Laposta
     */
    private $laposta;

    /**
     * @var ListMap
     */
    private $listMap;

    /**
     * @var string
     */
    private $apiToken;

    /**
     * @var ClientData
     */
    private $clientData;

    /**
     * @var FactoryInterface
     */
    private $iteratorFactory;

    /**
     * @var LockableInterface
     */
    private $lock;

    /**
     * @param Google            $google
     * @param Laposta           $laposta
     * @param Config            $config
     * @param FactoryInterface  $iteratorFactory
     * @param LockableInterface $lock
     */
    function __construct(
        Google $google,
        Laposta $laposta,
        Config $config,
        FactoryInterface $iteratorFactory,
        LockableInterface $lock
    ) {
        $this->config          = $config;
        $this->google          = $google;
        $this->laposta         = $laposta;
        $this->iteratorFactory = $iteratorFactory;
        $this->lock            = $lock;
    }

    /**
     * @param string $apiToken
     *
     * @return SyncFromGoogle
     */
    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * @param ListMap $listMap
     *
     * @return SyncFromGoogle
     */
    public function setListMap(ListMap $listMap)
    {
        $this->listMap = $listMap;

        return $this;
    }

    /**
     * @return ListMap
     */
    public function getListMap()
    {
        return $this->listMap;
    }

    /**
     * @param \Connect\Entity\ClientData $clientData
     *
     * @return SyncFromGoogle
     */
    public function setClientData($clientData)
    {
        $this->clientData = $clientData;

        return $this;
    }

    /**
     * @return \Connect\Entity\ClientData
     */
    public function getClientData()
    {
        return $this->clientData;
    }

    /**
     * Execute the command
     *
     * @return CommandInterface
     */
    public function execute()
    {
        $this->logger->warning("Removing all lists and corresponding contents from Laposta");

        \Laposta::setApiKey($this->clientData->lapostaApiToken);

        $this->laposta->removeLists();
        $this->clientData->lastImport = 0;
    }
}
