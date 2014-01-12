<?php

require __DIR__ . '/vendor/autoload.php';

try {
    $bs = new Bootstrap(new \Depend\Manager(), __DIR__, 'Connect\MVC\Main');
    $bs->loadGlobals(__DIR__ . '/src/global');
    $bs->boot();
}
catch (\Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');

    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
