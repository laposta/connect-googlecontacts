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
     * Boot the application
     */
    public function boot()
    {
        $this->initEnvironment();
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
     * Get the configuration
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
                require $this->projectRoot . '/config.php',
                @include $this->projectRoot . '/config.local.php',
            )
        );

        return $this->config = $this->dm->get('Config\Config');
    }

    /**
     * Initialize the dependency manager descriptors
     */
    protected function initDependencies()
    {
        $config = $this->getConfig();


        $this->dm->implement(
            'Web\Route\Abstraction\DependencyContainerInterface',
            'Web\Route\DependManagerProxy'
        );

        $this->dm->describe(
            'Path\Resolver',
            array(
                $config->get('path.application'),
                $config->get('path.document'),
                '/',
                '/tmp',
            )
        );

        $this->dm->describe(
            'Security\Cryptograph',
            array(
                $config->get('security.encryption_key')
            )
        );
    }

    /**
     * Initialize the applications' environment
     */
    protected function initEnvironment()
    {
        $config = $this->getConfig();

        date_default_timezone_set($config->get('timezone'));
    }
}
