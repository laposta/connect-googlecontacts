<?php

namespace Connect\MVC;

use Config\Config;
use Connect\Entity\ClientData;
use Connect\MVC\Base\Controller;
use Connect\MVC\Base\Model;
use Connect\MVC\Base\View;
use Connect\MVC\Model\Sync as SyncModel;
use Exception\ExceptionList;
use Exception;
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
        else if ($action === 'RESET') {
            $this->resetLaposta();
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
     */
    protected function importFromGoogle()
    {
        $status = 'ok';

        try {
            $this->model->importFromGoogle(
                $this->request->post('email'),
                $this->request->post('lapostaApiToken')
            );
        }
        catch (Exception $e) {
            $this->logger->error("{$e->getMessage()} on line '{$e->getLine()}' of '{$e->getFile()}'");
            $status = 'failed';
        }

        $returnUrl = $this->request->post('returnUrl');

        if (!empty($returnUrl)) {
            $this->redirect($returnUrl . (strpos($returnUrl, '?') !== false ? '&' : '?') . 'status=' . $status);
        }

        return $this;
    }

    /**
     * @return Sync
     */
    protected function resetLaposta()
    {
        $status = 'ok';

        try {
            $this->model->resetLaposta(
                $this->request->post('email'),
                $this->request->post('lapostaApiToken'),
                $this->request->post('hard') === 1
            );
        }
        catch (Exception $e) {
            $this->logger->error("{$e->getMessage()} on line '{$e->getLine()}' of '{$e->getFile()}'");
            $status = 'failed';
        }

        $returnUrl = $this->request->post('returnUrl');

        if (!empty($returnUrl)) {
            $this->redirect($returnUrl . (strpos($returnUrl, '?') !== false ? '&' : '?') . 'status=' . $status);
        }

        return $this;
    }

    /**
     * @return Sync
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


