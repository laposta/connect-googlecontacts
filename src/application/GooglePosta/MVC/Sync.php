<?php

namespace GooglePosta\MVC;

use Config\Config;
use GooglePosta\MVC\Base\Controller;
use GooglePosta\MVC\Base\View;
use GooglePosta\MVC\Model\Sync as SyncModel;
use Path\Resolver;
use Web\Exception\RuntimeException;
use Web\Response\Status;
use Web\Web;

class Sync extends Controller
{
    /**
     * @var SyncModel
     */
    protected $model;

    /**
     * @param Web       $web
     * @param SyncModel $model
     * @param View      $view
     * @param Config    $config
     * @param Resolver  $pathResolver
     */
    function __construct(
        Web $web,
        SyncModel $model,
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
        $action = '';

        if (!empty($params['action'])) {
            $action = strtoupper(filter_var($params['action'], FILTER_SANITIZE_STRING));
        }

        if ($action === 'IMPORT') {
            $this->importContacts();
        }
        else if ($action === 'CONSUME') {
            $this->consumeEvents();
        }
        else {
            throw new RuntimeException("Unrecognized action '$action'");
        }

        $this->respond(Status::OK);
    }

    /**
     * @throws \Web\Exception\RuntimeException
     *
     * @return Sync
     */
    protected function importContacts()
    {
        $email     = $this->getValidatedEmail();
        $apiToken  = $this->getValidatedApiToken();
        $returnUrl = $this->getValidatedReturnUrl();

        $this->model->clientToken = $this->model->createClientToken($email);
        $this->model->loadClientData();

        if ($this->model->clientData->lapostaApiToken !== $apiToken) {
            throw new RuntimeException('Token mismatch. You are not permitted to perform this action.');
        }

        // TODO : initiate synchronisation of contacts from Google

        if (!empty($returnUrl)) {
            $this->redirect($returnUrl);
        }

        return $this;
    }

    /**
     * @return Sync
     */
    protected function consumeEvents()
    {
        // TODO : process events.

        $data = $this->request->put();

        error_log(json_encode($data));

        return $this;
    }
}


