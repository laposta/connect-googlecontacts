<?php

if (!function_exists('array_column')) {
    /**
     * Return the values from a single column in the input array. PHP 5.5 array_column polyfill
     *
     * @param array $array
     * @param mixed $column_key
     * @param mixed $index_key
     *
     * @return array
     */
    function array_column(array $array, $column_key, $index_key = null)
    {
        $result = array();

        foreach ($array as $key => $value) {
            if (!isset($value[$column_key])) {
                continue;
            }

            if (!is_null($index_key)) {
                if (!isset($value[$index_key])) {
                    continue;
                }

                $key = $value[$index_key];
            }

            $result[$key] = $value[$column_key];
        }

        return $result;
    }
}
