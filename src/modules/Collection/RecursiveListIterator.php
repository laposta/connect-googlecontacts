<?php

namespace Collection;

use ArrayAccess;
use ArrayIterator;
use Collection\Abstraction\ArrayManageable;
use Collection\Abstraction\BackwardTraversable;
use Collection\Abstraction\Clearable;
use Countable;
use Iterator;
use RecursiveIterator;
use SeekableIterator;
use Serializable;
use Traversable;

class RecursiveListIterator extends ListIterator implements RecursiveIterator
{
    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Returns if an iterator can be created for the current entry.
     *
     * @link http://php.net/manual/en/recursiveiterator.haschildren.php
     * @return bool true if the current entry can be iterated over, otherwise returns false.
     */
    public function hasChildren()
    {
        $current = $this->current();

        return $current instanceof Traversable || is_array($current);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Returns an iterator for the current entry.
     *
     * @link http://php.net/manual/en/recursiveiterator.getchildren.php
     * @return RecursiveIterator An iterator for the current entry.
     */
    public function getChildren()
    {
        $current = $this->current();

        if ($current instanceof Iterator) {
            return $current;
        }

        if ($current instanceof Traversable || is_array($current)) {
            return new ArrayIterator($current);
        }

        return null;
    }
}
