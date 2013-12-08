<?php

return array(
    'timezone'    => 'Europe/Amsterdam',
    'environment' => 'production',
    'debug'       => array(
        'header_location' => false,
        'print_backtrace' => false,
    ),
    'path'        => array(
        'application' => __DIR__,
        'document'    => __DIR__ . '/public',
        'data'        => __DIR__ . '/data',
    ),
    'google'      => array(
        'client_secret' => 'PH9keaXHD0rDDgAV1qSpy_E-',
        'client_id'     => '915139937104-frrse699unjsdkmr30kmsvboi1g5hghm.apps.googleusercontent.com',
        'return_url'    => 'https://dev01.laposta-infra.nl/authority/',
    ),
    'security'    => array(
        'https'          => true,
        'encryption_key' => 'WPOzabpK2V2JGm70Hr091Nl2IUTyD2SgM98fNZEy',
    ),
);
