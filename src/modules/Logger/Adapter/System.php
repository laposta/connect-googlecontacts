<?php

namespace Logger\Adapter;

use Logger\Adapter\Abstraction\AdapterInterface;

class System implements AdapterInterface
{
    /**
     * @param string $level
     * @param string $log
     *
     * @return void
     */
    public function send($level, $log)
    {
        error_log('[' . strtoupper($level) . '] ' . $log);
    }
}
