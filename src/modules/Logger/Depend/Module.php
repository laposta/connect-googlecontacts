<?php

namespace Logger\Depend;

use Depend\Abstraction\ModuleInterface;
use Depend\Manager;

class Module implements ModuleInterface
{
    /**
     * Register the modules classes and interfaces with Depend\Manager
     *
     * @param Manager $dm
     *
     * @return void
     */
    public function register(Manager $dm)
    {
        /*
         * Set the classes for LoggerInterface and logger AdapterInterface
         */
        $dm->implement('Logger\Adapter\Abstraction\AdapterInterface', 'Logger\Adapter\File');
        $dm->implement('Logger\Abstraction\LoggerInterface', 'Logger\Logger');
    }
}
