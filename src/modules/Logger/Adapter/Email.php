<?php

namespace Logger\Adapter;

use Logger\Adapter\Abstraction\AdapterInterface;

class Email implements AdapterInterface
{
    /**
     * @var array
     */
    private $recipients;

    /**
     * @var array
     */
    private $headers;

    /**
     * Default constructor
     *
     * @param string|string[] $recipients
     * @param string          $from
     *
     * @throws \RuntimeException
     */
    function __construct($recipients, $from)
    {
        $fromEmail = filter_var($from, FILTER_VALIDATE_EMAIL);

        if (empty($fromEmail)) {
            throw new \RuntimeException("Given email address '$from' is not valid.");
        }

        if (!is_array($recipients)) {
            $recipients = array($recipients);
        }

        $recipientEmails = filter_var_array($recipients, FILTER_VALIDATE_EMAIL);

        if (empty($recipientEmails)) {
            $recipientsStr = implode(',', $recipients);

            throw new \RuntimeException("Given recipients '$recipientsStr' contains no valid email addresses.");
        }

        $this->recipients = $recipients;
        $this->headers    = array(
            'From: ' . $from . "\r\n",
            'MIME-Version: 1.0' . "\r\n",
            'Content-Type: text/text; charset=UTF8' . "\r\n",
        );
    }

    /**
     * @param string $level
     * @param string $log
     *
     * @return void
     */
    public function send($level, $log)
    {
        array_push(
            $this->headers,
            'Subject: A(n) ' . strtoupper($level) . ' event occurred' . "\r\n"
        );

        error_log(
            wordwrap($log . "\n"),
            1,
            implode(', ', $this->recipients),
            implode('', $this->headers)
        );
    }
}
