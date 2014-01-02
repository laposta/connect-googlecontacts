<?php

namespace GooglePosta\MVC;

use Config\Config;
use GooglePosta\MVC\Base\Controller;
use GooglePosta\MVC\Model\Cli as CliModel;
use GooglePosta\MVC\View\Cli as CliView;
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
     * @param Web      $web
     * @param CliModel $model
     * @param CliView  $view
     * @param Config   $config
     * @param Resolver $pathResolver
     */
    function __construct(
        Web $web,
        CliModel $model,
        CliView $view,
        Config $config,
        Resolver $pathResolver
    ) {
        parent::__construct($web, $model, $view, $config, $pathResolver);
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
