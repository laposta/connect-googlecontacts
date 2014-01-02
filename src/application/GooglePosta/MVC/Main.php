<?php

namespace GooglePosta\MVC;

use GooglePosta\MVC\Base\Controller;
use GooglePosta\MVC\View;

/**
 * Class Main
 *
 * @package GooglePosta\MVC
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
        $this->router->define('/authority/?action', 'GooglePosta\MVC\Authority');
        $this->router->define('/sync/:action', 'GooglePosta\MVC\Sync');
        $this->router->define('/cli/:action', 'GooglePosta\MVC\Cli');
        $this->router->catchall('GooglePosta\MVC\CatchAll');

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

        $this->defineRoutes()->route($path);
    }
}
