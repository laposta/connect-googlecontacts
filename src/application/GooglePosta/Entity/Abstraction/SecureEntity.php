<?php

namespace GooglePosta\Entity\Abstraction;

use Entity\Entity;
use Security\CryptographInterface;

class SecureEntity extends Entity implements SecureEntityInterface
{
    /**
     * @inheritdoc
     */
    public function encode(CryptographInterface $cryptograph, array $ignore = array())
    {
        foreach ($this as $key => $value) {
            if (in_array($key, $ignore)) {
                continue;
            }

            $this->$key = base64_encode($cryptograph->encode($value));
        }
    }

    /**
     * @inheritdoc
     */
    public function decode(CryptographInterface $cryptograph, array $ignore = array())
    {
        foreach ($this as $key => $value) {
            if (in_array($key, $ignore)) {
                continue;
            }

            $this->$key = $cryptograph->encode(base64_decode($value));
        }
    }
} 
