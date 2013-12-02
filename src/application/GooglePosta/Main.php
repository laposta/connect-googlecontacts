<?php

namespace GooglePosta;

use GooglePosta\MVC\Controller;
use Web\Route\Router;

class Main extends Controller
{
    /**
     * Setup the routing rules
     */
    protected function defineRoutes()
    {
        $this->router->setRouteType(Router::ROUTE_TYPE_DOMAIN);
        $this->router->define('my.', 'Totally200\Admin\Main');
        $this->router->define('get.totally200.', 'Totally200\Subscribe\Main');
        $this->router->catchall('Totally200\Website\Main');
    }

    /**
     * Run the controller
     *
     * @param array $params
     */
    public function run($params = array())
    {
        $this->defineRoutes();
        $this->route($this->request->domain());
    }
}
