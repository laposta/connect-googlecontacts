<?php

if (function_exists('pre_dump')) {
    return;
}

/**
 * var_dump equivalent with a html <pre> tag wrapper.
 */
function pre_dump()
{
    echo '<pre>'."\n";
    call_user_func_array('var_dump', func_get_args());
    echo '</pre>'."\n";
}
