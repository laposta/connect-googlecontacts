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

class Authority extends Model
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var string
     */
    private $clientToken;

    /**
     * @var \GooglePosta\Entity\ClientData
     */
    private $clientData;

    /**
     * @param \GooglePosta\Entity\ClientData $clientData
     *
     * @return Authority
     */
    public function setClientData(ClientData $clientData)
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
     * @param string $clientToken
     *
     * @return Authority
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
     * Constructor override
     *
     * @param CommandFactory $commandFactory
     * @param Session        $session
     */
    public function __construct(CommandFactory $commandFactory, Session $session)
    {
        parent::__construct($commandFactory);

        $this->session     = $session;
        $this->clientToken = $session->get('client.token');

        $this->loadClientData();
    }

    /**
     * @return Authority
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
     * Persist changes to the model.
     *
     * @return Authority
     */
    public function persist()
    {
        $this->session->set('client.token', $this->clientToken);
        $this->storeClientData();

        return $this;
    }

    /**
     * @return Authority
     */
    protected function storeClientData()
    {
        /** @var $command StoreClientData */
        $command = $this->commandFactory->create('GooglePosta\Command\StoreClientData');
        $command->setClientToken($this->clientToken)->setClientData($this->clientData)->execute();

        return $this;
    }

    /**
     * @return Authority
     */
    public function purgeClientData()
    {
        /** @var $command PurgeClientData */
        $command = $this->commandFactory->create('GooglePosta\Command\PurgeClientData');
        $command->setClientToken($this->clientToken)->execute();

        return $this;
    }

    /**
     * @return string
     */
    public function getGoogleAuthUrl()
    {
        /** @var $command InitializeApiBridge */
        $command = $this->commandFactory->create('GooglePosta\Command\InitializeApiBridge');
        $command->execute();

        return $command->getRedirectUrl();
    }

    /**
     * @param $googleAuthCode
     *
     * @return array array('access' => '', 'refresh' => '');
     */
    public function getGoogleTokens($googleAuthCode)
    {
        /** @var $command ConfirmApiBridge */
        $command = $this->commandFactory->create('GooglePosta\Command\ConfirmApiBridge');
        $command->setAuthCode($googleAuthCode)->execute();

        return $command->getTokens();
    }
}
