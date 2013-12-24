<?php

namespace Iterator\Abstraction;

use ArrayIterator;
use Iterator\ArrayPathIterator;
use Iterator\LinkedKeyIterator;
use Iterator\MultiLinkedKeyIterator;
use Traversable;

interface FactoryInterface
{
    /**
     * @param array|Traversable $array
     *
     * @return ArrayIterator
     */
    public function createArrayIterator($array = array());

    /**
     * @param array|Traversable $array
     *
     * @return ArrayPathIterator
     */
    public function createArrayPathIterator($array = array());

    /**
     * @param array|Traversable $array
     *
     * @return LinkedKeyIterator
     */
    public function createLinkedKeyIterator($array = array());

    /**
     * @param array|Traversable $array
     *
     * @return MultiLinkedKeyIterator
     */
    public function createMultiLinkedKeyIterator($array = array());
}
