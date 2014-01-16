<?php

namespace Security;

interface CryptographInterface
{
    /**
     * Encode the given string.
     *
     * @param string $string
     *
     * @return string
     */
    public function encode($string);

    /**
     * Decode the given encoded string.
     *
     * @param string $string
     *
     * @return string
     */
    public function decode($string);

    /**
     * Check if encryption is available.
     *
     * @return bool
     */
    public function isAvailable();
}
