<?php

namespace Iterator\Depend;

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
        $classes = array(
            'Iterator\ArrayIterator',
            'Iterator\ArrayPathIterator',
            'Iterator\LinkedKeyIterator',
            'Iterator\MultiLinkedKeyIterator',
        );

        foreach ($classes as $class) {
            $dm->describe($class)->setIsShared(false)->setIsCloneable(false);
        }

        $dm->implement('Iterator\Abstraction\IteratorFactoryInterface', 'Iterator\IteratorFactory');
    }
}
