<?php

namespace GooglePosta\MVC;

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
     * @var string
     */
    protected $layoutRoot = '_default/html/layout';

    /**
     * @var string
     */
    protected $pageRoot = '_default/html/page';

    /**
     * @param Web      $web
     * @param Model    $model
     * @param View     $view
     * @param Config   $config
     * @param Resolver $pathResolver
     */
    function __construct(Web $web, Model $model, View $view, Config $config, Resolver $pathResolver)
    {
        parent::__construct($web, $model, $view);

        $this->config = $config;
        $this->path   = $pathResolver;
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
     * @param string $relativePath
     *
     * @return string
     */
    protected function resolveLayout($relativePath)
    {
        return $this->resolveDocument($relativePath, $this->layoutRoot);
    }

    /**
     * @param string $relativePath
     *
     * @return string
     */
    protected function resolvePage($relativePath)
    {
        return $this->resolveDocument($relativePath, $this->pageRoot);
    }

    /**
     * @param string $relativePath
     * @param string $relativeRoot
     *
     * @return string
     */
    protected function resolveDocument($relativePath, $relativeRoot)
    {
        return $this->path->document(trim($relativeRoot, '/') . '/' . ltrim($relativePath, '/'));
    }

    /**
     * @param int             $errCode
     * @param \Exception|null $exception
     */
    protected function error($errCode, $exception = null)
    {
        $this->respond("/error/{$errCode}.phtml", null, $errCode);
    }

    /**
     * Respond with a 404 page not found error
     */
    protected function err404()
    {
        $this->error(404);
    }

    /**
     * Send the 200 response with the specified page and layout
     *
     * @param string $page   Page path relative to pageRoot
     * @param string $layout Layout path relative to layoutRoot [optional: default: '/default.phtml']
     * @param int    $statusCode
     */
    protected function respond($page, $layout = null, $statusCode = null)
    {
        if (is_null($layout)) {
            $layout = '/default.phtml';
        }

        if (is_null($statusCode)) {
            $statusCode = Status::OK;
        }

        $this->view->setLayout($this->resolveLayout($layout));
        $this->view->setPage($this->resolvePage($page));

        $this->response->respond(new Status($statusCode), $this->view->toString());
    }
}
