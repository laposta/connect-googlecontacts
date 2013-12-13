<?php

namespace GooglePosta\MVC;

use Config\Config;
use GooglePosta\MVC\Base\Controller;
use GooglePosta\MVC\Base\View;
use GooglePosta\MVC\Model\Authority as AuthorityModel;
use Path\Resolver;
use Web\Exception\RuntimeException;
use Web\Response\Status;
use Web\Web;

class Authority extends Controller
{
    /**
     * @var AuthorityModel
     */
    protected $model;

    /**
     * @param Web            $web
     * @param AuthorityModel $model
     * @param View           $view
     * @param Config         $config
     * @param Resolver       $pathResolver
     */
    function __construct(
        Web $web,
        AuthorityModel $model,
        View $view,
        Config $config,
        Resolver $pathResolver
    ) {
        parent::__construct($web, $model, $view, $config, $pathResolver);
    }

    /**
     * Run the controller
     *
     * @param array $params
     *
     * @throws \Web\Exception\RuntimeException
     */
    public function run($params = array())
    {
        $requestMethod  = strtoupper($this->request->server('REQUEST_METHOD'));
        $googleAuthCode = filter_var($this->request->get('code'), FILTER_SANITIZE_STRING);
        $action         = '';

        if (!empty($params['action'])) {
            $action = strtoupper(filter_var($params['action'], FILTER_SANITIZE_STRING));
        }

        if (!empty($googleAuthCode)) {
            $this->confirmAuthority($googleAuthCode);
        }
        elseif ($requestMethod === 'DELETE' || $action === 'DELETE') {
            $this->purgeAuthority();
        }
        else {
            $this->initAuthority();
        }

        $this->respond(Status::OK);
    }

    /**
     * @throws \Web\Exception\RuntimeException
     * @return Authority
     */
    protected function purgeAuthority()
    {
        $email     = $this->getValidatedEmail();
        $apiToken  = $this->getValidatedApiToken();
        $returnUrl = $this->getValidatedReturnUrl();

        $this->model->clientToken = $this->model->createClientToken($email);
        $this->model->loadClientData();

        if ($this->model->clientData->lapostaApiToken !== $apiToken) {
            throw new RuntimeException('Token mismatch. You are not permitted to perform this action.');
        }

        $this->model->purgeClientData();

        if (!empty($returnUrl)) {
            $this->redirect($returnUrl);
        }

        return $this;
    }

    /**
     * @return Authority
     * @throws \Web\Exception\RuntimeException
     */
    protected function initAuthority()
    {
        $email     = $this->getValidatedEmail();
        $apiToken  = $this->getValidatedApiToken();
        $returnUrl = $this->getValidatedReturnUrl();

        $this->model->clientToken = $this->model->createClientToken($email);
        $this->model->loadClientData();

        $clientData                  = $this->model->clientData;
        $clientData->email           = $email;
        $clientData->lapostaApiToken = $apiToken;
        $clientData->returnUrl       = $returnUrl;

        $this->model->persist();

        $redirect = $this->model->retrieveGoogleAuthUrl();

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
    protected function confirmAuthority($googleAuthCode)
    {
        $tokens = $this->model->retrieveGoogleTokens($googleAuthCode);

        $clientData                     = $this->model->clientData;
        $clientData->googleAccessToken  = $tokens['access'];
        $clientData->googleRefreshToken = $tokens['refresh'];

        $this->model->persist();

        $redirect = $clientData->returnUrl;

        if (!empty($redirect)) {
            $this->redirect($redirect);
        }

        return $this;
    }
}


