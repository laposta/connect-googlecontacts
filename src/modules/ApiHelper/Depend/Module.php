<?php

namespace ApiHelper\Depend;

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
        $dm->implement('ApiHelper\Contacts\Abstraction\FactoryInterface', 'ApiHelper\Contacts\Factory');
        $dm->describe('ApiHelper\Contacts\Laposta')->setIsShared(false)->setIsCloneable(false);
        $dm->describe('ApiHelper\Contacts\Google')->setIsShared(false)->setIsCloneable(false);
    }
}
