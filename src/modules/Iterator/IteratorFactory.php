<?php

namespace Iterator;

use Iterator\Abstraction\IteratorFactoryInterface;
use Traversable;

class IteratorFactory implements IteratorFactoryInterface
{
    /**
     * @param array|Traversable $array
     *
     * @return ArrayIterator
     */
    public function createArrayIterator($array = array())
    {
        return new ArrayIterator($array);
    }

    /**
     * @param array|Traversable $array
     *
     * @return ArrayPathIterator
     */
    public function createArrayPathIterator($array = array())
    {
        return new ArrayPathIterator($array);
    }

    /**
     * @param array|Traversable $array
     *
     * @return LinkedKeyIterator
     */
    public function createLinkedKeyIterator($array = array())
    {
        return new LinkedKeyIterator($array);
    }

    /**
     * @param array|Traversable $array
     *
     * @return MultiLinkedKeyIterator
     */
    public function createMultiLinkedKeyIterator($array = array())
    {
        return new MultiLinkedKeyIterator($array);
    }
}
