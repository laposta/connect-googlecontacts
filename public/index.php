<?php

if (!isset($_SERVER['REQUEST_TIME_FLOAT'])) {
    $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);
}

require_once __DIR__ . '/../bootstrap.php';

echo '<!--' . "\n";
echo 'Page generated in: ';
echo round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000) . 'ms' . "\n";
echo '-->' . "\n";
