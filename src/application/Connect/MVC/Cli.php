<?php

namespace Connect\MVC;

use Cli\CliHelper;
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
     * @var CliHelper
     */
    protected $cli;

    /**
     * @param Web             $web
     * @param CliModel        $model
     * @param CliView         $view
     * @param Config          $config
     * @param Resolver        $pathResolver
     * @param LoggerInterface $logger
     * @param CliHelper       $cli
     */
    function __construct(
        Web $web,
        CliModel $model,
        CliView $view,
        Config $config,
        Resolver $pathResolver,
        LoggerInterface $logger,
        CliHelper $cli
    ) {
        parent::__construct($web, $model, $view, $config, $pathResolver, $logger);

        $this->cli = $cli;
    }

    /**
     * Run the controller
     *
     * @param array $params
     */
    public function run($params = array())
    {
        if ($this->cli->isCli() && $this->cli->countProcesses() > 1) {
            $this->logger->info("Command '{$this->cli->getCurrentCommand()}' is already running. Exiting.");

            return;
        }

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
