<?php

require __DIR__ . '/vendor/autoload.php';

try {
    $bs = new Bootstrap(new \Depend\Manager(), __DIR__, 'GooglePosta\Main');
    $bs->loadGlobals(__DIR__ . '/src/global');
    $bs->boot();
}
catch (\Exception $e) {
    header('Status: 500 Internal Server Error');

    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
