<?php

if (!isset($_SERVER['REQUEST_TIME_FLOAT'])) {
    $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);
}

try {
    require_once __DIR__ . '/../bootstrap.php';
}
catch (Exception $e) {
    // NO-OP
}

echo '<!--' . "\n";
echo 'processing duration: ' . round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000) . ' ms' . "\n";
echo 'peak memory usage: ' . memory_get_peak_usage(true) / 1024 / 1024 . ' MB' . "\n";
echo '-->' . "\n";
