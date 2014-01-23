<?php

namespace Connect\Entity\Abstraction;

use Entity\Entity;
use Security\CryptographInterface;

class SecureEntity extends ClearableEntity implements SecureEntityInterface
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
     * Return a list of properties to skip/ignore when encoding/decoding.
     *
     * @return array
     */
    protected function ignore()
    {
        return array();
    }
}
