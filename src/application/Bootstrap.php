<?php

use Config\Config;

class Bootstrap
{
    /**
     * @var Depend\Manager
     */
    protected $dm;

    /**
     * @var string
     */
    protected $projectRoot;

    /**
     * @var string
     */
    protected $appController;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Default constructor
     *
     * @param \Depend\Manager $dm
     * @param string          $projectRoot
     * @param string          $appController
     *
     * @throws RuntimeException
     */
    public function __construct(Depend\Manager $dm, $projectRoot, $appController)
    {
        if (!($dm instanceof Depend\Manager)) {
            throw new RuntimeException('Bootstrap expects the dependency manager. Unable to boot.');
        }

        $this->dm            = $dm;
        $this->projectRoot   = $projectRoot;
        $this->appController = $appController;
    }

    /**
     * Load any php files found in the given path.
     *
     * @param $path
     *
     * @return $this
     * @throws RuntimeException
     */
    public function loadGlobals($path)
    {
        if (!file_exists($path)) {
            throw new RuntimeException("Unable to load globals from '$path'. Given path is not a file or directory.");
        }

        if (!is_dir($path)) {
            require "$path";

            return $this;
        }

        $directory = new RecursiveDirectoryIterator($path);
        $iterator  = new RecursiveIteratorIterator($directory);
        $list      = new RegexIterator($iterator, '/\.php$/i');

        for ($list->rewind(); $list->valid(); $list->next()) {
            /** @var $file SplFileInfo */

            $file     = $list->current();
            $filePath = $file->getRealPath();

            require "$filePath";
        }

        return $this;
    }

    /**
     * Boot the application
     */
    public function boot()
    {
        $this->initEnvironment();
        $this->initModules();
        $this->initDependencies();

        $main = $this->dm->get($this->appController);

        if (!($main instanceof \MVC\Controller)) {
            throw new RuntimeException('Bootstrap is unable to load the given application controller');
        }

        try {
            $main->run();
        }
        catch (\Exception $e) {
            $main->err($e);
        }
    }

    /**
     * Initialize the applications' environment
     */
    protected function initEnvironment()
    {
        $config = $this->getConfig();

        /*
         * Always run the script to completion
         */
        ignore_user_abort(true);

        date_default_timezone_set($config->get('timezone'));
        ini_set('memory_limit', $config->get('memory_limit'));
        set_time_limit($config->get('time_limit'));
    }

    /**
     * Register modules with the dependency manager
     */
    private function initModules()
    {
        $this->dm->module('Iterator\Depend\Module');
        $this->dm->module('Logger\Depend\Module');
        $this->dm->module('ApiHelper\Depend\Module');
    }

    /**
     * Get the configuration from config.php and config.local.php
     *
     * @return Config
     */
    protected function getConfig()
    {
        if ($this->config instanceof Config) {
            return $this->config;
        }

        /** @noinspection PhpIncludeInspection */
        $this->dm->describe(
            'Config\Config',
            array(
                 $this->dm->describe('Iterator\ArrayPathIterator'),
                 require $this->projectRoot . '/config.php',
                 @include $this->projectRoot . '/config.local.php',
            )
        );

        return $this->config = $this->dm->get('Config\Config');
    }

    /**
     * Initialize the dependency manager descriptors and setup default parameters and injectors.
     */
    protected function initDependencies()
    {
        /** @var $injectorFactory \Depend\InjectorFactory */
        $injectorFactory = $this->dm->get('Depend\InjectorFactory');
        $config          = $this->getConfig();

        /*
         * Configure the logger
         */
        $this->dm->describe(
            'Logger\Logger',
            array(
                 $config->get('debug.log_level'),
                 /*
                 $this->dm->describe('Logger\Adapter\Output', array('html' => (php_sapi_name() !== 'cli'))),
                 /*/
                 $this->dm->describe('Logger\Adapter\File', array($config->get('path.log'))),
                 //*/
            )
        );

        /*
         * Set the class for DependencyContainerInterface dependencies
         */
        $this->dm->implement('Web\Route\Abstraction\DependencyContainerInterface', 'Web\Route\DependManagerProxy');

        /*
         * Set parameters for the Path\Resolver class
         */
        $this->dm->describe(
            'Path\Resolver',
            array(
                 $config->get('path.application'),
                 $config->get('path.document'),
                 '/',
                 '/tmp',
            )
        );

        /*
         * Set parameters for the Security\Cryptograph class
         */
        $this->dm->describe(
            'Security\Cryptograph',
            array(
                 $config->get('security.encryption_key')
            )
        );

        /*
         * Set some injector calls for the Google_Client class
         */
        $this->dm->describe(
            'Google_Client',
            null,
            array(
                 $injectorFactory->create('setClientId', $config->get('google.client_id')),
                 $injectorFactory->create('setClientSecret', $config->get('google.client_secret')),
                 $injectorFactory->create('setRedirectUri', $config->get('google.return_url')),
                 $injectorFactory->create('setScopes', $config->get('google.scopes')),
            )
        );

        /*
         * Set the default locks directory and default class for LockableInterface
         */
        $this->dm->implement('Lock\Abstraction\LockableInterface', 'Lock\Lock');
        $this->dm->describe('Lock\Lock', array($config->get('path.lock')));
    }
}
