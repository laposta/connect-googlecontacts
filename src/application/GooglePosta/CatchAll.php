<?php

namespace GooglePosta;

use GooglePosta\MVC\Controller;

class CatchAll extends Controller
{
    /**
     * Run the controller
     *
     * @param array $params
     */
    public function run($params = array())
    {
        $this->err404();
    }
}
