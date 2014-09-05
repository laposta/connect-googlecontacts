<?php

namespace Connect\MVC\Model;

use Command\CommandFactory;
use Connect\Command\ConfirmApiBridge;
use Connect\Command\InitializeApiBridge;
use Connect\Command\LoadClientData;
use Connect\Command\PurgeClientData;
use Connect\Command\StoreClientData;
use Connect\Entity\ClientData;
use Connect\MVC\Base\Model;
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
     * @var \Connect\Entity\ClientData
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
    }

    /**
     * Confirm authority with google with provided auth code.
     *
     * @param string $googleAuthCode
     *
     * @return bool
     * @throws \RuntimeException
     */
    public function confirmAuthority($googleAuthCode)
    {
        if (empty($googleAuthCode)) {
            throw new RuntimeException("Unable to authorise bridge with auth code '$googleAuthCode'");
        }

        $this->loadClientData();

        /** @var $command ConfirmApiBridge */
        $command = $this->getCommandFactory()->create('Connect\Command\ConfirmApiBridge');
        $command->setAuthCode($googleAuthCode)->execute();

        $this->clientData->googleTokenSet     = $command->getTokens();
        $this->clientData->googleRefreshToken = $this->clientData->googleTokenSet->refresh_token;
        $this->clientData->authGranted        = true;

        $this->session->destroy();

        return $this->persist();
    }

    /**
     * Get the clients return url
     *
     * @return string
     */
    public function getClientReturnUrl()
    {
        if (!($this->clientData instanceof ClientData)) {
            $this->loadClientData();
        }

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

        $this->clientData->token           = $this->clientToken;
        $this->clientData->email           = $email;
        $this->clientData->lapostaApiToken = $apiToken;
        $this->clientData->returnUrl       = $returnUrl;

        $this->persist();

        /** @var $command InitializeApiBridge */
        $command = $this->getCommandFactory()->create('Connect\Command\InitializeApiBridge');
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

        if (is_empty($this->clientData->lapostaApiToken)) {
            /** @var $command PurgeClientData */
            $command = $this->getCommandFactory()->create('Connect\Command\PurgeClientData');
            $command->setClientToken($this->clientToken)->execute();

            return $this;
        }
        else if ($this->clientData->lapostaApiToken !== $apiToken) {
            throw new RuntimeException('Token mismatch. You are not permitted to perform this action.');
        }

        /** @var $command PurgeClientData */
        $command = $this->getCommandFactory()->create('Connect\Command\PurgeClientData');
        $command->setClientToken($this->clientToken)->execute();

        /** @var $command PurgeClientMap */
        $command = $this->getCommandFactory()->create('Connect\Command\PurgeClientMap');
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
        $command = $this->getCommandFactory()->create('Connect\Command\LoadClientData');
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
        $command = $this->getCommandFactory()->create('Connect\Command\StoreClientData');
        $command->setClientToken($this->clientToken)->setClientData($this->clientData)->execute();

        return $this;
    }
}
