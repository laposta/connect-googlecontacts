<?php

namespace Exception\Abstraction;

use Exception;

interface ExceptionListInterface
{
    /**
     * The number of exceptions in the list.
     *
     * @return int
     */
    public function count();

    /**
     * Append an exception to the list.
     *
     * @param Exception $exception
     *
     * @return $this
     */
    public function append(Exception $exception);

    /**
     * Get messages for all exceptions in list
     *
     * @return array
     */
    public function getMessages();

    /**
     * Get codes for all exceptions in list
     *
     * @return array
     */
    public function getCodes();

    /**
     * Get files for all exceptions in list
     *
     * @return array
     */
    public function getFiles();

    /**
     * Get line number for all exceptions in list
     *
     * @return array
     */
    public function getLines();

    /**
     * Get traces for all exceptions in list
     *
     * @return array
     */
    public function getTraces();

    /**
     * Get traces for all exceptions in list as a string
     *
     * @return string
     */
    public function getTracesAsString();

    /**
     * Get the list of exceptions
     *
     * @return array
     */
    public function getList();
}
