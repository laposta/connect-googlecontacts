<?php

if (!function_exists('is_empty')) {
    /**
     * Finds whether the given variable is empty. This function
     * may be used for return values from function calls not
     * permissible as a parameter to the native empty() function.
     *
     * @param mixed $var
     *
     * @return bool
     */
    function is_empty($var)
    {
        return empty($var);
    }
}

