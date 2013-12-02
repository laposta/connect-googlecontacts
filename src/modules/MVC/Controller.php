<?php

namespace MVC;

use Web\Request\Request;
use Web\Response\Response;
use Web\Route\Router;
use Web\Web;

abstract class Controller
{
    /**
     * @var Web
     */
    protected $web;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var View
     */
    protected $view;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @param \Web\Web $web
     * @param Model    $model
     * @param View     $view
     */
    function __construct(Web $web, Model $model, View $view)
    {
        $this->web      = $web;
        $this->request  = $web->getRequest();
        $this->response = $web->getResponse();
        $this->router   = $web->getRouter();
        $this->model    = $model;
        $this->view     = $view;
    }

    /**
     * Run the controller
     *
     * @param array $params
     */
    abstract public function run($params = array());
}
