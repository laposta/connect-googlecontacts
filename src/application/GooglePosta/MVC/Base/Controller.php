<?php

namespace GooglePosta\MVC\Base;

use Command\CommandFactory;
use Config\Config;
use Path\Resolver;
use Web\Response\Status;
use Web\Web;

/**
 * Class Controller
 *
 * @package Totally200\MVC
 */
abstract class Controller extends \MVC\Controller
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Resolver
     */
    protected $path;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var View
     */
    protected $view;

    /**
     * @var CommandFactory
     */
    protected $commandFactory;

    /**
     * @param Web            $web
     * @param Model          $model
     * @param View           $view
     * @param Config         $config
     * @param Resolver       $pathResolver
     * @param CommandFactory $commandFactory
     */
    function __construct(
        Web $web,
        Model $model,
        View $view,
        Config $config,
        Resolver $pathResolver,
        CommandFactory $commandFactory
    ) {
        parent::__construct($web, $model, $view);

        $this->config         = $config;
        $this->path           = $pathResolver;
        $this->commandFactory = $commandFactory;
    }

    /**
     * Match the routing rules and run its corresponding controller
     *
     * @param string $pathOrDomain
     */
    protected function route($pathOrDomain)
    {
        $controller = $this->router->match($pathOrDomain);

        if ($controller instanceof Controller) {
            $controller->run($this->router->getMatchParams());
        }
    }

    /**
     * @param int             $errCode
     * @param \Exception|null $exception
     */
    protected function error($errCode, $exception = null)
    {
        if ($exception instanceof \Exception) {
            $this->view->setContent($exception->getMessage() . "\n");
        }

        $this->respond($errCode);
    }

    /**
     * Send the response with output from the view
     *
     * @param int $statusCode
     */
    protected function respond($statusCode = null)
    {
        if (is_null($statusCode)) {
            $statusCode = Status::OK;
        }

        $status  = new Status($statusCode);
        $content = $this->view->toString();

        if (empty($content)) {
            $content = $status->getStatusText();
        }

        $this->response->respond($status, $content);
    }
}
