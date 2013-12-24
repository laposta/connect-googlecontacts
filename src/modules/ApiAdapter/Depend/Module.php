<?php

namespace ApiAdapter\Depend;

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
        $dm->implement('ApiAdapter\Contacts\Abstraction\FactoryInterface', 'ApiAdapter\Contacts\Factory');
    }
}
