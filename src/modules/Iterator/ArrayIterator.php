<?php

namespace Iterator;

use ArrayIterator as BaseArrayIterator;
use Iterator\Abstraction\ArrayTransferableInterface;
use Traversable;

class ArrayIterator extends BaseArrayIterator implements ArrayTransferableInterface
{
    /**
     * @return array
     */
    public function toArray()
    {
        return $this->getArrayCopy();
    }

    /**
     * @param array|Traversable $traversable
     *
     * @return $this
     */
    public function fromArray($traversable)
    {
        foreach ($this as $key => $value) {
            unset($this[$key]);
        }

        if (!is_array($traversable) && !($traversable instanceof Traversable)) {
            return $this;
        }

        foreach ($traversable as $key => $value) {
            $this[$key] = $value;
        }

        return $this;
    }
}
