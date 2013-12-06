<?php

namespace GooglePosta;

use GooglePosta\MVC\Controller;

class Main extends Controller
{
    /**
     * Setup the routing rules
     */
    protected function defineRoutes()
    {
        $this->router->catchall('GooglePosta\CatchAll');
    }

    /**
     * Run the controller
     *
     * @param array $params
     */
    public function run($params = array())
    {
        $this->defineRoutes();
        $this->route($this->request->uri());
    }
}
