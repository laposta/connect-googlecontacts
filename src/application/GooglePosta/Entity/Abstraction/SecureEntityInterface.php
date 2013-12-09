<?php

namespace GooglePosta\Entity\Abstraction;

use Security\CryptographInterface;

interface SecureEntityInterface
{
    /**
     * Encode all values of the entity using the provided cryptograph
     *
     * @param CryptographInterface $cryptograph
     * @param array                $ignore List of keys to ignore
     *
     * @return SecureEntityInterface
     */
    public function encode(CryptographInterface $cryptograph, array $ignore = array());

    /**
     * Decode all values of the entity using the provided cryptograph
     *
     * @param CryptographInterface $cryptograph
     * @param array                $ignore List of keys to ignore
     *
     * @return SecureEntityInterface
     */
    public function decode(CryptographInterface $cryptograph, array $ignore = array());
} 
