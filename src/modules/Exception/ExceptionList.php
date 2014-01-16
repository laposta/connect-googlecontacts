<?php

namespace Exception;

use ArrayIterator;
use Exception;
use Exception\Abstraction\ExceptionListInterface;

class ExceptionList extends Exception implements ExceptionListInterface
{
    /**
     * @var ArrayIterator
     */
    private $list;

    /**
     * Default constructor
     *
     * @param array $list
     */
    function __construct(array $list = array())
    {
        parent::__construct('Multiple exceptions occurred.');

        $this->list = new ArrayIterator($list);
    }

    /**
     * The number of exceptions in the list.
     *
     * @return int
     */
    public function count()
    {
        return $this->list->count();
    }

    /**
     * Append an exception to the list.
     *
     * @param Exception $exception
     *
     * @return $this
     */
    public function append(Exception $exception)
    {
        $this->list->append($exception);

        return $this;
    }

    /**
     * Get messages for all exceptions in list
     *
     * @return array
     */
    public function getMessages()
    {
        $result = array();

        /** @var $exception Exception */
        foreach ($this->list as $exception) {
            $result[] = $exception->getMessage();
        }

        return $result;
    }

    /**
     * Get codes for all exceptions in list
     *
     * @return array
     */
    public function getCodes()
    {
        $result = array();

        /** @var $exception Exception */
        foreach ($this->list as $exception) {
            $result[] = $exception->getCode();
        }

        return $result;
    }

    /**
     * Get files for all exceptions in list
     *
     * @return array
     */
    public function getFiles()
    {
        $result = array();

        /** @var $exception Exception */
        foreach ($this->list as $exception) {
            $result[] = $exception->getFile();
        }

        return $result;
    }

    /**
     * Get line number for all exceptions in list
     *
     * @return array
     */
    public function getLines()
    {
        $result = array();

        /** @var $exception Exception */
        foreach ($this->list as $exception) {
            $result[] = $exception->getLine();
        }

        return $result;
    }

    /**
     * Get traces for all exceptions in list
     *
     * @return array
     */
    public function getTraces()
    {
        $result = array();

        /** @var $exception Exception */
        foreach ($this->list as $exception) {
            $result[] = $exception->getTrace();
        }

        return $result;
    }

    /**
     * Get traces for all exceptions in list as a string
     *
     * @return string
     */
    public function getTracesAsString()
    {
        $result = array();

        /** @var $exception Exception */
        foreach ($this->list as $exception) {
            $result[] = $exception->getTraceAsString();
        }

        return implode("\n---\n", $result);
    }

    /**
     * Get the list of exceptions
     *
     * @return array
     */
    public function getList()
    {
        return $this->list->getArrayCopy();
    }
}
