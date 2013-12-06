<?php

set_error_handler(
    function ($errNo, $errStr, $errFile, $errLine) {
        throw new ErrorException($errStr, $errNo, 0, $errFile, $errLine);
    }
);
