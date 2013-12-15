<?php

namespace GooglePosta\Entity\Abstraction;

use Entity\Entity;
use Security\CryptographInterface;

class SecureEntity extends Entity implements SecureEntityInterface
{
    /**
     * @inheritdoc
     */
    public function encode(CryptographInterface $cryptograph)
    {
        $ignore = $this->ignore();

        foreach ($this as $key => $value) {
            if (in_array($key, $ignore) || empty($value)) {
                continue;
            }

            if ($value instanceof SecureEntityInterface) {
                $value->encode($cryptograph);

                continue;
            }

            $this->$key = base64_encode($cryptograph->encode($value));
        }
    }

    /**
     * @inheritdoc
     */
    public function decode(CryptographInterface $cryptograph)
    {
        $ignore = $this->ignore();

        foreach ($this as $key => $value) {
            if (in_array($key, $ignore) || empty($value)) {
                continue;
            }

            if ($value instanceof SecureEntityInterface) {
                $value->decode($cryptograph);

                continue;
            }

            $this->$key = $cryptograph->decode(base64_decode($value));
        }
    }

    /**
     * @inheritdoc
     */
    public function ignore()
    {
        return array();
    }
}
