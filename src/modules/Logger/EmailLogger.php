<?php

namespace Logger;

use Logger\Abstraction\AbstractLogger;

class EmailLogger extends AbstractLogger
{
    /**
     * @var string
     */
    private $logRecipients;

    /**
     * @var string
     */
    private $headers;

    /**
     * Constructor override
     *
     * @param mixed           $logLevel
     * @param string|string[] $recipients
     */
    function __construct($logLevel, $recipients)
    {
        parent::__construct($logLevel);

        $this->logRecipients = is_array($recipients) ? implode(',', $recipients) : $recipients;

        $this->headers = "From: no-reply@laposta.com\r\n" .
            "MIME-Version: 1.0\r\n" .
            "Content-Type: text/plain; charset=UTF8\r\n";
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function log($level, $message, $context = array())
    {
        if (!$this->levelAccepted($level)) {
            return;
        }

        error_log(
            wordwrap($this->getTimeString() . ' ' . trim($this->interpolate($message, $context)) . "\n"),
            1,
            $this->logRecipients,
            $this->headers
        );
    }
}
