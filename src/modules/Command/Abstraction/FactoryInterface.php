<?php

namespace Command\Abstraction;

interface FactoryInterface 
{
    /**
     * @param string $className
     *
     * @return CommandInterface
     */
    public function create($className);
} 
