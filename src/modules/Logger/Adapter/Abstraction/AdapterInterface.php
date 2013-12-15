<?php

namespace Logger\Adapter\Abstraction;

interface AdapterInterface 
{
    /**
     * @param string $level
     * @param string $log
     *
     * @return void
     */
    public function send($level, $log);
}
