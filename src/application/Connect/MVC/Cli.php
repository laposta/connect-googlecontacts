<?php

namespace Connect\MVC;

use Config\Config;
use Connect\MVC\Base\Controller;
use Connect\MVC\Model\Cli as CliModel;
use Connect\MVC\View\Cli as CliView;
use Logger\Abstraction\LoggerInterface;
use Path\Resolver;
use Web\Web;

class Cli extends Controller
{
    /**
     * @var CliView
     */
    protected $view;

    /**
     * @var CliModel
     */
    protected $model;

    /**
     * @param Web             $web
     * @param CliModel        $model
     * @param CliView         $view
     * @param Config          $config
     * @param Resolver        $pathResolver
     * @param LoggerInterface $logger
     */
    function __construct(
        Web $web,
        CliModel $model,
        CliView $view,
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
     */
    public function run($params = array())
    {
        $action = '';

        if (isset($params['action'])) {
            $action = strtoupper($params['action']);
        }

        if ($action === 'NO-OP') {
            $this->view->printHelp();

            return;
        }

        if ($action === 'IMPORT') {
            $this->importFromGoogle();
        }
    }

    /**
     * @return Sync
     * @throws \RuntimeException
     */
    protected function importFromGoogle()
    {
        $this->model->importFromGoogle($this->config->get('path.data'));

        return $this;
    }
}
