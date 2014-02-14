<?php

namespace Connect\MVC;

use Cli\CliHelper;
use Config\Config;
use Connect\MVC\Base\Controller;
use Connect\MVC\Base\Model;
use Connect\MVC\Base\View;
use Logger\Abstraction\LoggerInterface;
use Path\Resolver;
use Web\Web;

/**
 * Class Main
 *
 * @package Connect\MVC
 */
class Main extends Controller
{
    /**
     * @var CliHelper
     */
    protected $cli;

    /**
     * @param Web             $web
     * @param Model           $model
     * @param View            $view
     * @param Config          $config
     * @param Resolver        $pathResolver
     * @param LoggerInterface $logger
     * @param CliHelper       $cli
     */
    function __construct(
        Web $web,
        Model $model,
        View $view,
        Config $config,
        Resolver $pathResolver,
        LoggerInterface $logger,
        CliHelper $cli
    ) {
        parent::__construct($web, $model, $view, $config, $pathResolver, $logger);

        $this->cli = $cli;
    }

    /**
     * Setup the routing rules
     *
     * @return Main
     */
    protected function defineRoutes()
    {
        $this->router->define('/authority/?action', 'Connect\MVC\Authority');
        $this->router->define('/sync/:action', 'Connect\MVC\Sync');
        $this->router->define('/cli/:action', 'Connect\MVC\Cli');
        $this->router->catchall('Connect\MVC\CatchAll');

        return $this;
    }

    /**
     * Run the controller
     *
     * @param array $params
     */
    public function run($params = array())
    {
        if ($this->cli->isCli()) {
            $action = $this->cli->getArg(1);

            if (empty($action)) {
                $action = 'no-op';
            }

            $path   = "/cli/$action";
        }
        else {
            $path = $this->request->uri()->getPath();
        }

        $this->logger->debug("Receive request with path '$path'");

        $this->defineRoutes()->route($path);
    }
}
