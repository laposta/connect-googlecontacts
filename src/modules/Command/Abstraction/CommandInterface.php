<?php

namespace Command\Abstraction;

interface CommandInterface 
{
    /**
     * Execute the command
     *
     * @return CommandInterface
     */
    public function execute();
} 
