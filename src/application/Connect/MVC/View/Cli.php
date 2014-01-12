<?php

namespace GooglePosta\MVC\View;

use GooglePosta\MVC\Base\View;

class Cli extends View
{
    public function printHelp()
    {
        global $argv;

        echo 'Usage: ' . $argv[0] . ' import' . "\n";
    }
} 
