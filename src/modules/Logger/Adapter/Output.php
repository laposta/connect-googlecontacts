<?php

namespace Logger\Adapter;

use Logger\Abstraction\LogLevel;
use Logger\Adapter\Abstraction\AdapterInterface;

class Output implements AdapterInterface
{
    /**
     * @var bool
     */
    private $html = true;

    /**
     * @var array
     */
    private $styleMap = array(
        LogLevel::ANY       => '',
        LogLevel::EMERGENCY => 'color: #570099; font-weight: bold;',
        LogLevel::ALERT     => 'color: #990250; font-weight: bold;',
        LogLevel::CRITICAL  => 'color: #990E03; font-weight: bold;',
        LogLevel::ERROR     => 'color: #993600; font-weight: bold;',
        LogLevel::WARNING   => 'color: #946B00;',
        LogLevel::NOTICE    => 'color: #6D9900;',
        LogLevel::INFO      => 'color: #003299;',
        LogLevel::DEBUG     => 'color: #007A99;',
    );

    /**
     * @param bool $html
     */
    function __construct($html = true)
    {
        $this->html = $html;
    }

    /**
     * @param string $level
     * @param string $log
     *
     * @return void
     */
    public function send($level, $log)
    {
        if ($this->html) {
            $style = '';

            if (isset($this->styleMap[$level])) {
                $style = $this->styleMap[$level];
            }

            echo "<pre style=\"margin: 0; color: #007a99; $style\">";
        }

        echo '[' . strtoupper($level) . '] ' . $log . "\n";

        if ($this->html) {
            echo '</pre>';
        }
    }
}
