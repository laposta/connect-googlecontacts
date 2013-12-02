<?php

namespace Template\Abstraction;

interface ElementFactoryInterface 
{
    /**
     * @param string $className
     *
     * @return AbstractElement
     */
    public function get($className);
} 
