<?php

namespace Connect\MVC\View;

use Connect\MVC\Base\View;

class Cli extends View
{
    public function printHelp()
    {
        global $argv;

        echo 'Usage: ' . $argv[0] . ' import' . "\n";
        echo 'Usage: ' . $argv[0] . ' list' . "\n";
        echo 'Usage: ' . $argv[0] . ' list {customerId}' . "\n";
        echo 'Usage: ' . $argv[0] . ' restore {customerId} {fromListId} {toListId}' . "\n";
    }
} 
