<?php

namespace Connect\MVC;

use Connect\MVC\Base\Controller;
use Connect\MVC\View;

/**
 * Class Main
 *
 * @package Connect\MVC
 */
class Main extends Controller
{
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
        if (php_sapi_name() === 'cli') {
            global $argv;

            $action = isset($argv[1]) ? $argv[1] : 'no-op';
            $path   = "/cli/$action";
        }
        else {
            $path = $this->request->uri()->getPath();
        }

        $this->logger->debug("Receive request with path '$path'");

        $this->defineRoutes()->route($path);
    }
}
