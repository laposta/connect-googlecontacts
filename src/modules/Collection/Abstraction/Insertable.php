<?php

namespace Collection\Abstraction;

interface Insertable
{
    /**
     * Insert the given value into the list at the current position.
     * Current position is bumped up to compensate.
     *
     * @param mixed $value
     *
     * @return Insertable
     */
    public function insert($value);
}
