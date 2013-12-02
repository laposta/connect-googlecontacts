<?php

namespace Collection\Abstraction;

interface ArrayManageable
{
    /**
     * Pop a value off the end of the list.
     *
     * @return ArrayManageable
     */
    public function pop();

    /**
     * Push a value onto the end of the list.
     *
     * @param mixed $value
     *
     * @return ArrayManageable
     */
    public function push($value);

    /**
     * Shift a value from the start of the list.
     *
     * @return ArrayManageable
     */
    public function shift();

    /**
     * Unshift a value onto the start of the list.
     *
     * @param mixed $value
     *
     * @return ArrayManageable
     */
    public function unshift($value);

    /**
     * Return the list as an array
     *
     * @return array
     */
    public function getArrayCopy();

    /**
     * Sort the list by value
     *
     * @return ArrayManageable
     */
    public function asort();

    /**
     * Sort the list by key
     *
     * @return ArrayManageable
     */
    public function ksort();

    /**
     * Sort the list naturally by value, case insensitive
     *
     * @return ArrayManageable
     */
    public function natcasesort();

    /**
     * Sort the list naturally by value
     *
     * @return ArrayManageable
     */
    public function natsort();

    /**
     * Sort the list by value using user defined function.
     *
     * @param callable $cmpFunction
     *
     * @return ArrayManageable
     */
    public function uasort($cmpFunction);

    /**
     * Sort the list by key using user defined function.
     *
     * @param callable $cmpFunction
     *
     * @return ArrayManageable
     */
    public function uksort($cmpFunction);
}
