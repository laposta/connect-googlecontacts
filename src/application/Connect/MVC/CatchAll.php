<?php

namespace Connect\MVC;

use Connect\MVC\Base\Controller;

class CatchAll extends Controller
{
    /**
     * Run the controller
     *
     * @param array $params
     */
    public function run($params = array())
    {
        $this->error(404);
    }
}
