<?php

namespace GooglePosta\MVC;

use Config\Config;
use GooglePosta\Entity\ClientData;
use GooglePosta\MVC\Base\Controller;
use GooglePosta\MVC\Base\Model;
use GooglePosta\MVC\Base\View;
use GooglePosta\MVC\Model\Sync as SyncModel;
use Logger\Abstraction\LoggerInterface;
use Path\Resolver;
use RuntimeException;
use Web\Response\Status;
use Web\Web;

class Sync extends Controller
{
    /**
     * @var SyncModel
     */
    protected $model;

    /**
     * @param Web             $web
     * @param SyncModel       $model
     * @param View            $view
     * @param Config          $config
     * @param Resolver        $pathResolver
     * @param LoggerInterface $logger
     */
    function __construct(
        Web $web,
        SyncModel $model,
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
     * @throws \RuntimeException
     */
    public function run($params = array())
    {
        $action = '';

        if (isset($params['action'])) {
            $action = strtoupper($params['action']);
        }

        if ($action === 'IMPORT') {
            $this->importFromGoogle();
        }
        else if ($action === 'CONSUME-EVENTS') {
            $this->consumeEvents();
        }
        else {
            throw new RuntimeException("Unrecognized action '$action'");
        }

        $this->respond(Status::OK);
    }

    /**
     * @return Sync
     * @throws \RuntimeException
     */
    protected function importFromGoogle()
    {
        $this->model->importFromGoogle(
            $this->request->post('email'),
            $this->request->post('lapostaApiToken')
        );

        $returnUrl = $this->request->post('returnUrl');

        if (!empty($returnUrl)) {
            $this->redirect($returnUrl);
        }

        return $this;
    }

    /**
     * @return Sync
     * @throws \RuntimeException
     */
    protected function consumeEvents()
    {
        $this->model->consumeEvents(
            $this->request->get('clientToken'),
            $this->request->put()
        );

        return $this;
    }
}


