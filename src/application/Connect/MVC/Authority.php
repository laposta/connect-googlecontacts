<?php

namespace Connect\MVC;

use Config\Config;
use Connect\MVC\Base\Controller;
use Connect\MVC\Base\View;
use Connect\MVC\Model\Authority as AuthorityModel;
use Exception;
use Logger\Abstraction\LoggerInterface;
use Path\Resolver;
use Web\Response\Status;
use Web\Web;

class Authority extends Controller
{
    /**
     * @var AuthorityModel
     */
    protected $model;

    /**
     * @param Web             $web
     * @param AuthorityModel  $model
     * @param View            $view
     * @param Config          $config
     * @param Resolver        $pathResolver
     * @param LoggerInterface $logger
     */
    function __construct(
        Web $web,
        AuthorityModel $model,
        View $view,
        Config $config,
        Resolver $pathResolver,
        LoggerInterface $logger
    ) {
        parent::__construct($web, $model, $view, $config, $pathResolver, $logger);
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
        $requestMethod = strtoupper($this->request->server('REQUEST_METHOD'));
        $action        = '';

        if (isset($params['action'])) {
            $action = strtoupper($params['action']);
        }

        if (!is_empty($this->request->get('code')) || !is_empty($this->request->get('error'))) {
            $this->confirmAuthority();
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
        $status = 'ok';

        try {
            $this->model->purgeAuthority(
                $this->request->post('email'),
                $this->request->post('lapostaApiToken')
            );
        }
        catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $status = 'failed';
        }

        $returnUrl = $this->request->post('returnUrl');

        if (!empty($returnUrl)) {
            $this->redirect($returnUrl . (strpos($returnUrl, '?') !== false ? '&' : '?') . 'status=' . $status);
        }

        return $this;
    }

    /**
     * @return Authority
     * @throws \Web\Exception\RuntimeException
     */
    protected function initAuthority()
    {
        $status = 'ok';

        try {
            $redirect = $this->model->initiate(
                $this->request->post('email'),
                $this->request->post('lapostaApiToken'),
                $this->request->post('returnUrl')
            );

            if (!empty($redirect)) {
                $this->redirect($redirect);

                    return $this;
            }
        }
        catch(Exception $e) {
            $this->logger->error($e->getMessage());
            $status = 'failed';
        }

        $returnUrl = $this->request->post('returnUrl');

        if (!empty($returnUrl)) {
            $this->redirect($returnUrl . (strpos($returnUrl, '?') !== false ? '&' : '?') . 'status=' . $status);
        }

        return $this;
    }

    /**
     * @return Authority
     */
    protected function confirmAuthority()
    {
        $status = 'ok';

        try {
            $this->model->confirmAuthority($this->request->get('code'));
        }
        catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $status = 'failed';
        }

        $returnUrl = $this->model->getClientReturnUrl();

        if (!empty($returnUrl)) {
            $this->redirect($returnUrl . (strpos($returnUrl, '?') !== false ? '&' : '?') . 'status=' . $status);
        }


        return $this;
    }
}


