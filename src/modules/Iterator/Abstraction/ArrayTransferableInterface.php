<?php

namespace Iterator\Abstraction;

use Traversable;

interface ArrayTransferableInterface
{
    /**
     * @return array
     */
    public function toArray();

    /**
     * @param array|Traversable $traversable
     *
     * @return $this
     */
    public function fromArray($traversable);
} 
