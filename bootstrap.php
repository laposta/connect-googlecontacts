<?php

require __DIR__ . '/vendor/autoload.php';

$bs = new Bootstrap(new \Depend\Manager(), __DIR__, 'GooglePosta\Main');

$bs->loadGlobals(__DIR__ . '/src/global');
$bs->boot();
