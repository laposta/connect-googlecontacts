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

        if (!empty($googleAuthCode)) {
            $this->confirmAuthority($googleAuthCode);
        }
        elseif ($requestMethod === 'DELETE') {
            $this->purgeAuthority();
        }
        else {
            $this->initAuthority();
        }

        $this->respond(Status::OK);
    }

    /**
     * @param string $identifier
     *
     * @return string
     */
    protected function makeClientToken($identifier)
    {
        return sha1($identifier);
    }

    /**
     * @throws \Web\Exception\RuntimeException
     * @return Authority
     */
    protected function purgeAuthority()
    {
        $email     = filter_var($this->request->get('email'), FILTER_VALIDATE_EMAIL);
        $returnUrl = filter_var($this->request->get('returnUrl'), FILTER_VALIDATE_URL);

        if (empty($this->email)) {
            throw new RuntimeException("Input not valid. Expected a valid 'email' value");
        }

        $this->model->clientToken = $this->makeClientToken($email);
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
        $email     = filter_var($this->request->post('email'), FILTER_VALIDATE_EMAIL);
        $apiToken  = filter_var($this->request->post('lapostaApiToken'), FILTER_SANITIZE_STRING);
        $returnUrl = filter_var($this->request->post('returnUrl'), FILTER_VALIDATE_URL);

        if (empty($email)) {
            throw new RuntimeException("Input not valid. Expected a valid 'email' value");
        }

        if (empty($apiToken)) {
            throw new RuntimeException("Input not valid. Expected a valid 'lapostaApiToken' value");
        }

        $this->model->clientToken = $this->makeClientToken($email);
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


