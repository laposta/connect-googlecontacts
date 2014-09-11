<?php

namespace Connect\MVC;

use ApiHelper\Contacts\Entity\Group;
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

        if ($action === 'LIST') {
            $clientId = '';

            if ($this->cli->getArgCount() > 2) {
                $clientId = $this->cli->getArg(2);
            }

            $this->showInfoList($clientId);
        }

        if ($action === 'RESTORE') {
            if ($this->cli->getArgCount() < 5) {
                $this->view->printHelp();

                return;
            }

            $clientId  = $this->cli->getArg(2);
            $fromListId = $this->cli->getArg(3);
            $toListId = $this->cli->getArg(4);

            $this->restoreListMappings($clientId, $fromListId, $toListId);
        }
    }

    /**
     * @return Cli
     * @throws \RuntimeException
     */
    protected function importFromGoogle()
    {
        $this->model->importFromGoogle($this->config->get('path.data'));

        return $this;
    }

    /**
     * Show a list of registered customers or if given a customerId a list of mailing lists for the customer
     *
     * @param string $clientId
     */
    protected function showInfoList($clientId = '')
    {
        if (empty($clientId)) {
            $list = $this->model->buildClientList($this->config->get('path.data'));

            foreach ($list as $index => $clientId) {
                echo "$index => $clientId \n";
            }
        }
        else {
            $list = $this->model->buildMailingListList($clientId);

            echo "Mailing lists for customer with id '$clientId': \n";

            /** @var $group Group */
            foreach ($list as $group) {
                echo "{$group->lapId} => {$group->title}\n";
            }
        }
    }

    /**
     * Restore mappings from a new mailing list back into an old mailing list
     *
     * @param $clientId
     * @param $fromListId
     * @param $toListId
     *
     * @return Cli
     */
    protected function restoreListMappings($clientId, $fromListId, $toListId)
    {
        echo "Restoring customer mappings for '$clientId' from list '$fromListId' to '$toListId': \n";

        $this->model->rebuildClientMap($clientId, $fromListId, $toListId);
    }
}
