<?php

if (!function_exists('is_traversable')) {
    /**
     * Finds whether the given variable is traversable
     *
     * @param mixed $var
     *
     * @return bool
     */
    function is_traversable($var)
    {
        return is_array($var) || $var instanceof Traversable;
    }
}
