<?php

namespace ApiHelper\Contacts\Exception;

use Exception;
use Laposta_Error;

class UnknownListException extends \Exception
{
    /**
     * @var string
     */
    protected $listId;

    /**
     * @param string    $message
     * @param int       $code
     * @param Exception $previous
     * @param string    $listId
     */
    public function __construct($message = '', $code = 0, Exception $previous = null, $listId = '')
    {
        parent::__construct($message, $code, $previous);

        $this->listId = $listId;
    }

    /**
     * @return string
     */
    public function getListId()
    {
        return $this->listId;
    }

}
