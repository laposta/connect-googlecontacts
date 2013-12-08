<?php

namespace GooglePosta\MVC;

use GooglePosta\MVC\Base\Controller;

class Main extends Controller
{
    /**
     * Setup the routing rules
     *
     * @return Main
     */
    protected function defineRoutes()
    {
        $this->router->define('/authority/?output', 'GooglePosta\MVC\Authority');
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
        $this->defineRoutes()->route($this->request->uri()->getPath());
    }
}
