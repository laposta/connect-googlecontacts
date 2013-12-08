<?php

namespace GooglePosta\MVC;

use GooglePosta\Command\ConfirmApiBridge;
use GooglePosta\Command\InitializeApiBridge;
use GooglePosta\Command\LoadClientData;
use GooglePosta\Command\PurgeClientData;
use GooglePosta\Command\StoreClientData;
use GooglePosta\Entity\ClientData;
use GooglePosta\MVC\Base\Controller;
use Web\Exception\RuntimeException;
use Web\Response\Status;

class Authority extends Controller
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $apiToken;

    /**
     * @var string
     */
    private $clientToken;

    /**
     * @var ClientData
     */
    private $clientData;

    /**
     * @var string
     */
    private $returnUrl;

    /**
     * Run the controller
     *
     * @param array $params
     *
     * @throws \Web\Exception\RuntimeException
     */
    public function run($params = array())
    {
        $requestMethod    = strtoupper($this->request->server('REQUEST_METHOD'));
        $googleAuthCode   = filter_var($this->request->get('code'), FILTER_SANITIZE_STRING);
        $this->email      = filter_var($this->request->post('email'), FILTER_VALIDATE_EMAIL);
        $this->apiToken   = filter_var($this->request->post('lapostaApiToken'), FILTER_SANITIZE_STRING);
        $this->returnUrl  = filter_var($this->request->post('returnUrl'), FILTER_VALIDATE_URL);

        if ($this->session->has('client.token')) {
            $this->setClientToken($this->session->get('client.token'));
        }
        else {
            $this->session->set('client.token', $this->getClientToken());
        }

        if (!empty($googleAuthCode)) {
            $this->confirmApiBridge($googleAuthCode);
        }
        elseif ($requestMethod === 'DELETE') {
            $this->purgeClientData();
        }
        else {
            $this->initApiBridge();
        }

        $this->respond(Status::OK);
    }

    /**
     * @param string $token
     */
    protected function setClientToken($token)
    {
        $this->clientToken = filter_var(
            $token,
            FILTER_VALIDATE_REGEXP,
            array(
                'options' => array(
                    'default' => null,
                    'regexp'  => '/^[0-9a-f]{40}$/',
                )
            )
        );
    }

    /**
     * @return string
     * @throws \Web\Exception\RuntimeException
     */
    protected function getClientToken()
    {
        if (!empty($this->clientToken)) {
            return $this->clientToken;
        }

        if (empty($this->email)) {
            throw new RuntimeException("Input not valid. Expected a valid 'email' value");
        }

        $this->clientToken = sha1($this->email);

        return $this->clientToken;
    }

    /**
     * @return ClientData
     */
    protected function getClientData()
    {
        if ($this->clientData instanceof ClientData) {
            return $this->clientData;
        }

        /** @var $command LoadClientData */
        $command = $this->commandFactory->create('GooglePosta\Command\LoadClientData');
        $command->setClientToken($this->getClientToken())->execute();

        $this->clientData = $command->getClientData();

        return $this->clientData;
    }

    /**
     * @return Authority
     */
    protected function storeClientData()
    {
        /** @var $command StoreClientData */
        $command = $this->commandFactory->create('GooglePosta\Command\StoreClientData');
        $command->setClientToken($this->getClientToken())->setClientData($this->getClientData())->execute();

        return $this;
    }

    /**
     * @return Authority
     */
    protected function purgeClientData()
    {
        /** @var $command PurgeClientData */
        $command = $this->commandFactory->create('GooglePosta\Command\PurgeClientData');
        $command->setClientToken($this->getClientToken())->execute();

        return $this;
    }

    /**
     * @return Authority
     * @throws \Web\Exception\RuntimeException
     */
    protected function initApiBridge()
    {
        if (empty($this->email)) {
            throw new RuntimeException("Input not valid. Expected a valid 'email' value");
        }

        if (empty($this->apiToken)) {
            throw new RuntimeException("Input not valid. Expected a valid 'lapostaApiToken' value");
        }

        $this->getClientData()->setEmail($this->email)->setLapostaApiToken($this->apiToken)->setReturnUrl(
            $this->returnUrl
        );

        /** @var $command InitializeApiBridge */
        $command = $this->commandFactory->create('GooglePosta\Command\InitializeApiBridge');
        $command->setClientData($this->getClientData())->execute();

        $this->storeClientData();

        $redirect = $command->getRedirectUrl();

        if (!empty($redirect)) {
            $this->redirect($redirect);
        }

        return $this;
    }

    /**
     * @param string $googleAuthCode
     *
     * @return Authority
     */
    protected function confirmApiBridge($googleAuthCode)
    {
        /** @var $command ConfirmApiBridge */
        $command = $this->commandFactory->create('GooglePosta\Command\ConfirmApiBridge');
        $command->setClientData($this->getClientData())->setAuthCode($googleAuthCode)->execute();

        $this->storeClientData();

        $redirect = $this->clientData->getReturnUrl();

        if (!empty($redirect)) {
            $this->redirect($redirect);
        }

        return $this;
    }

    /**
     * @param $url
     *
     * @return Authority
     */
    protected function redirect($url)
    {
        if ($this->config->get('debug.header_location')) {
            $this->view->setContent('<a href="' . $url . '" target="_blank">follow location header</a>');

            return $this;
        }

        $this->response->redirect($url);

        return $this;
    }
}


