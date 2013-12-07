<?php

return array(
    'timezone'    => 'Europe/Amsterdam',
    'environment' => 'production',
    'path'        => array(
        'application' => __DIR__,
        'document'    => __DIR__ . '/public',
        'data'        => __DIR__ . '/data',
    ),
    'google'      => json_decode(file_get_contents(__DIR__ . "/config.google.json"), true),
    'security'    => array(
        'encryption_key' => 'WPOzabpK2V2JGm70Hr091Nl2IUTyD2SgM98fNZEy',
    ),
);
