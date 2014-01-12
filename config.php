<?php

/*
 * ! CAUTION: DO NOT MODIFY VALUES IN THIS FILE, ONLY ADDING VALUES IS PERMISSIBLE
 *
 * This file contains default values for the application to be run without a customized configuration.
 * Only production values should be added to this file. A config.local.php file can be created alongside
 * this file where values can be overridden.
 *
 * Use the following command to safely create a copy for your application instance:
 *
 *     $ cp -n config.php config.local.php
 *
 * or for a documentation free copy use:
 *
 *     $ php -r "file_exists('config.local.php') || \
         file_put_contents('config.local.php', \"<?php\n\nreturn \".var_export(require 'config.php', true).\";\n\");"
 *
 * Use your preferred editor to modify the config.local.php file for your specific requirements.
 */

use Logger\Abstraction\LogLevel;

return array(
    /*
     * The applications environment.
     */
    'environment'  => 'production',
    /*
     * Timezone to be used for the application
     */
    'timezone'     => 'Europe/Amsterdam',
    /*
     * Set the allowed memory limit for the application
     */
    'memory_limit' => '512M',
    /*
     * Set the default time limit
     */
    'time_limit'   => 0,
    /*
     * Application Hostname
     */
    'hostname'     => 'googleposta.laposta.nl',
    /*
     * Application uses ssl (https)
     */
    'ssl' => true,
    /*
     * Debug settings
     */
    'debug'        => array(
        /*
         * Print a link to the location header instead of setting the location header.
         */
        'header_location' => false,
        /*
         * Print a backtrace when an error / exception occurs.
         */
        'print_backtrace' => false,
        /*
         * The log verbosity level for the application.
         */
        'log_level'       => LogLevel::ERROR,
    ),
    /*
     * Application paths
     */
    'path'         => array(
        /*
         * Path the application root.
         */
        'application' => __DIR__,
        /*
         * Path the applications document (web) root. This folder is publicly accessible to the www.
         */
        'document'    => __DIR__ . '/public',
        /*
         * Applications data directory
         */
        'data'        => __DIR__ . '/data',
        /*
         * Application log directory
         */
        'log'         => __DIR__ . '/logs',
        /*
         * Application log directory
         */
        'lock'         => __DIR__ . '/data/locks',
    ),
    /*
     * Configuration settings for access to Google APIs.
     */
    'google'       => array(
        /*
         * Client secret from google cloud console.
         */
        'client_secret' => 'PH9keaXHD0rDDgAV1qSpy_E-',
        /*
         * Client id from google cloud console.
         */
        'client_id'     => '915139937104-frrse699unjsdkmr30kmsvboi1g5hghm.apps.googleusercontent.com',
        /*
         * The url google will return to after consent screen
         */
        'return_url'    => 'https://googleposta.laposta.nl/authority/',
        /*
         * The google api scopes being used by this application.
         */
        'scopes'        => array(
            /*
             * Google Contacts
             */
            'https://www.google.com/m8/feeds/',
        ),
    ),
    /*
     * Security related settings.
     */
    'security'     => array(
        /*
         * Use https when generating urls for this application.
         */
        'https'          => true,
        /*
         * Key to use for data encryption.
         */
        'encryption_key' => 'WPOzabpK2V2JGm70Hr091Nl2IUTyD2SgM98fNZEy',
    ),
    /*
     * Settings for the synchronisation process
     */
    'sync'         => array(
        /*
         * Allow Google default system groups (i.e. My Contacts, Friends, Family, Coworkers) to
         * be imported. If 'false' only custom (user created) groups will be imported.
         */
        'allow_system_groups' => false,
    ),
);
