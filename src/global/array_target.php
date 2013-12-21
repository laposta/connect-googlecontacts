<?php

if (!function_exists('array_target')) {
    /**
     * Returns the desired array child specified in a dot notation string.
     * if target is not found null is returned.
     * i.e array_target(array('one'=>array('two'=>array('three'=>1))), "one.two")
     * will return a reference to the value of $array[one][two].
     *
     * @param array  $arr
     * @param string $targetStr
     *
     * @return mixed A reference to the value found for the specified target
     */
    function &array_target(&$arr, $targetStr = "")
    {
        if (is_null($targetStr) || $targetStr == '') {
            return $arr;
        }

        $targets = explode('.', $targetStr);

        foreach ($targets as $step) {
            if (isset($arr) && is_array($arr)) {
                $arr = & $arr[$step];
            }
            else {
                unset($arr);
            }
        }

        if (!isset($arr)) {
            $arr = null;
        }

        return $arr;
    }
}

if (!function_exists('array_target_set')) {
    /**
     * sets desired array child specified in a dot notation string.
     * if target is not found null is returned.
     * i.e  array_target(array('one'=>array('two'=>array('three'=>1))), "one.two") will return
     *      a reference to the value of $array[one][two].
     *
     * @param array  $arr
     * @param string $targetStr
     * @param mixed  $value
     *
     * @return mixed A reference to the created value for the specified target
     */
    function &array_target_set(&$arr, $targetStr, $value)
    {
        if (is_null($targetStr) || $targetStr == '') {
            $arr = $value;

            return $arr;
        }

        $targets = explode('.', $targetStr);

        while (count($targets) > 0) {
            $step = array_shift($targets);
            if (!isset($arr[$step])) {
                $arr[$step] = array();
            }
            $arr = & $arr[$step];
        }

        $arr = $value;

        return $arr;
    }
}
