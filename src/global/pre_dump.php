<?php

if (!function_exists('pre_dump')) {
    /**
     * var_dump equivalent with a html <pre> tag wrapper.
     *
     * @param mixed $var value to dump
     * @param mixed $var,... OPTIONAL additional values to dump
     */
    function pre_dump($var)
    {
        echo '<pre>'."\n";
        call_user_func_array('var_dump', func_get_args());
        echo '</pre>'."\n";
    }
}

if (!function_exists('pretty_dump')) {
    /**
     * Travers a data structure printing it's contents and path
     *
     * @param mixed  $data
     * @param string $prefix
     * @param bool   $wrap
     */
    function pretty_dump($data, $prefix = '', $wrap = true)
    {
        if ($wrap === true) {
            echo '<pre style="margin: 10px;">';
        }

        if (is_bool($data)) {
            $data = $data ? 'true' : 'false';

            echo "<span style=\"color:#090;\">$prefix</span> = <span style=\"color:#909;\">$data</span>\n";
        }
        else if (is_int($data)) {
            echo "<span style=\"color:#090;\">$prefix</span> = <span style=\"color:#009;\">$data</span>\n";
        }
        else if (empty($data)) {
            echo "<span style=\"color:#090;\">$prefix</span> = <span style=\"color:#999;\">empty</span>\n";
        }
        else if ((!is_array($data) && !($data instanceof \Traversable))) {
            echo "<span style=\"color:#090;\">$prefix</span> = <span style=\"color:#900;\">'$data'</span>\n";
        }
        else {
            if ($data instanceof ArrayIterator) {
                $data->ksort();
            }
            else if (is_array($data)) {
                ksort($data);
            }

            foreach ($data as $key => $value) {
                pretty_dump($value, trim("$prefix.$key", '.'), false);
            }
        }

        if ($wrap === true) {
            echo '</pre>';
        }
    }
}
