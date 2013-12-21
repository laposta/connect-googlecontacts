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
use RuntimeException;
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
     * Confirm authority with google with provided auth code.
     *
     * @param string $googleAuthCode
     *
     * @return Authority
     */
    public function confirmAuthority($googleAuthCode)
    {
        /** @var $command ConfirmApiBridge */
        $command = $this->getCommandFactory()->create('GooglePosta\Command\ConfirmApiBridge');
        $command->setAuthCode($googleAuthCode)->execute();

        $this->clientData->googleTokenSet     = $command->getTokens();
        $this->clientData->googleRefreshToken = $this->clientData->googleTokenSet->refresh_token;

        return $this->persist();
    }

    /**
     * Get the clients return url
     *
     * @return string
     */
    public function getClientReturnUrl()
    {
        return $this->clientData->returnUrl;
    }

    /**
     * Initiate the authority request with google
     *
     * @param string $email     Laposta account email
     * @param string $apiToken  Laposta API token
     * @param string $returnUrl URL for redirects back to Laposta
     *
     * @return string The google OAuth URL to redirect to.
     */
    public function initiate($email, $apiToken, $returnUrl)
    {
        $email     = filter_var($email, FILTER_SANITIZE_EMAIL);
        $apiToken  = filter_var($apiToken, FILTER_SANITIZE_STRING);
        $returnUrl = filter_var($returnUrl, FILTER_SANITIZE_URL);

        $this->validateEmail($email);
        $this->validateUrl($returnUrl);

        $this->clientToken = $this->createClientToken($email);

        $this->loadClientData();

        $this->clientData->email           = $email;
        $this->clientData->lapostaApiToken = $apiToken;
        $this->clientData->returnUrl       = $returnUrl;

        $this->persist();

        /** @var $command InitializeApiBridge */
        $command = $this->getCommandFactory()->create('GooglePosta\Command\InitializeApiBridge');
        $command->execute();

        return $command->getRedirectUrl();
    }

    /**
     * Purge a stored authority for the provided email / apiToken
     *
     * @param string $email    Laposta account email
     * @param string $apiToken Laposta API token
     *
     * @return Authority
     * @throws RuntimeException
     */
    public function purgeAuthority($email, $apiToken)
    {
        $email    = filter_var($email, FILTER_SANITIZE_EMAIL);
        $apiToken = filter_var($apiToken, FILTER_SANITIZE_STRING);

        $this->validateEmail($email);

        $this->clientToken = $this->createClientToken($email);

        $this->loadClientData();

        if ($this->clientData->lapostaApiToken !== $apiToken) {
            throw new RuntimeException('Token mismatch. You are not permitted to perform this action.');
        }

        /** @var $command PurgeClientData */
        $command = $this->getCommandFactory()->create('GooglePosta\Command\PurgeClientData');
        $command->setClientToken($this->clientToken)->execute();

        return $this;
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
        $command = $this->getCommandFactory()->create('GooglePosta\Command\LoadClientData');
        $command->setClientToken($this->clientToken)->execute();

        $this->clientData = $command->getClientData();

        return $this;
    }

    /**
     * Persist changes to the model.
     *
     * @return Authority
     */
    protected function persist()
    {
        $this->session->set('client.token', $this->clientToken);

        /** @var $command StoreClientData */
        $command = $this->getCommandFactory()->create('GooglePosta\Command\StoreClientData');
        $command->setClientToken($this->clientToken)->setClientData($this->clientData)->execute();

        return $this;
    }
}
