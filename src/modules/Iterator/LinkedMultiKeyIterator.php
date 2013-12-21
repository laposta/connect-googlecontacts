<?php

namespace Iterator;

/**
 * Class LinkedMultiKeyIterator
 * Performs the same as a LinkedKeyIterator with one addition. When multiple possible keys
 * are found for a given value, an array of corresponding keys is returned instead of just the
 * last found allowing for a many-to-one relationship.
 *
 * @package Iterator
 */
class LinkedMultiKeyIterator extends LinkedKeyIterator
{
    /**
     * Initialize the linked key iterator
     */
    protected function resetSecondary()
    {
        $flipped = array();

        foreach ($this as $key => $value) {
            if (!isset($flipped[$value])) {
                $flipped[$value] = array();
            }

            array_push($flipped[$value], $key);
        }

        $this->secondary = new ArrayIterator($flipped, $this->getFlags());
    }
}
