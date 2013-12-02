<?php

namespace Template\Abstraction;

use Printable;

interface NodeInterface extends Printable
{
    /**
     * @return Printable|string
     */
    public function getContent();

    /**
     * @param NodeInterface|string $value
     *
     * @return NodeInterface
     */
    public function addContent($value);
} 
