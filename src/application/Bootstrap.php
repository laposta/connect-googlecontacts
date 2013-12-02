<?php

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
        $this->init();

        $main = $this->dm->get($this->appController);

        if (!($main instanceof \MVC\Controller)) {
            throw new RuntimeException('Bootstrap is unable to load the given application controller');
        }

        $main->run();
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
     * Get the configuration object
     *
     * @return Config\Config
     */
    protected function getConfig()
    {
        /** @noinspection PhpIncludeInspection */

        $this->dm->describe(
            'Config\Config',
            array(
                require $this->projectRoot . '/config.php',
                @include $this->projectRoot . '/config.local.php',
            )
        );

        return $this->dm->get('Config\Config');
    }

    /**
     * Initialize the dependency manager descriptors
     */
    protected function init()
    {
        $config = $this->getConfig();

        $this->dm->implement(
            'Web\Route\Abstraction\DependencyContainerInterface',
            'Web\Route\DependManagerProxy'
        );

        $this->dm->implement(
            'Template\Abstraction\ElementFactoryInterface',
            'Template\ElementFactory'
        );

        $this->dm->describe('Template\Node\Collection\NodeList')->setIsShared(false);
        $this->dm->describe('Template\Node\Collection\AttributeList')->setIsShared(false);

        $this->dm->describe(
            'PDO',
            array(
                $config->get('database.dsn'),
                $config->get('database.username'),
                $config->get('database.password'),
            )
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

        if ($config->get('environment') === 'development') {
            $this->dm->describe('Template\IncludeResolver', array(true));
        }
    }
}
