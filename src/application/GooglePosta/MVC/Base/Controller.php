<?php

namespace GooglePosta\MVC\Base;

use Command\CommandFactory;
use Config\Config;
use Path\Resolver;
use Session\Session;
use Web\Exception\RuntimeException;
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
     * @var Session
     */
    protected $session;

    /**
     * @param Web              $web
     * @param Model            $model
     * @param View             $view
     * @param Config           $config
     * @param Resolver         $pathResolver
     */
    function __construct(
        Web $web,
        Model $model,
        View $view,
        Config $config,
        Resolver $pathResolver
    ) {
        parent::__construct($web, $model, $view);

        $this->config         = $config;
        $this->path           = $pathResolver;
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

        $this->response->respond($status, $content);
    }

    /**
     * Handle an error
     *
     * @param \Exception $e
     */
    public function err(\Exception $e)
    {
        echo "<pre>\n";

        echo $e->getMessage() . "\n";

        if ($this->config->get('debug.print_backtrace')) {
            echo $e->getTraceAsString() . "\n";
        }

        echo "</pre>\n";

        $this->response->respond(new Status(Status::INTERNAL_SERVER_ERROR));
    }

    /**
     * @param $url
     *
     * @return Controller
     */
    protected function redirect($url)
    {
        if ($this->config->get('debug.header_location')) {
            $this->view->setContent('<a href="' . $url . '">follow location header</a>');

            return $this;
        }

        $this->response->redirect($url);

        return $this;
    }


    /**
     * @return string
     * @throws RuntimeException
     */
    protected function getValidatedEmail()
    {
        $email = filter_var($this->request->post('email'), FILTER_VALIDATE_EMAIL);

        if (empty($email)) {
            throw new RuntimeException("Input not valid. Expected a valid 'email' value");
        }

        return $email;
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    protected function getValidatedApiToken()
    {
        $apiToken  = filter_var($this->request->post('lapostaApiToken'), FILTER_SANITIZE_STRING);

        if (empty($apiToken)) {
            throw new RuntimeException("Input not valid. Expected a valid 'lapostaApiToken' value");
        }

        return $apiToken;
    }

    /**
     * @return string
     */
    protected function getValidatedReturnUrl()
    {
        $returnUrl = filter_var($this->request->post('returnUrl'), FILTER_VALIDATE_URL);

        return $returnUrl;
    }
}
