<?php

namespace Connect\Entity\Abstraction;

use Security\CryptographInterface;

interface SecureEntityInterface
{
    /**
     * Encode all values of the entity using the provided cryptograph
     *
     * @param CryptographInterface $cryptograph
     *
     * @return SecureEntityInterface
     */
    public function encode(CryptographInterface $cryptograph);

    /**
     * Decode all values of the entity using the provided cryptograph
     *
     * @param CryptographInterface $cryptograph
     *
     * @return SecureEntityInterface
     */
    public function decode(CryptographInterface $cryptograph);
}
