<?php

namespace Collection\Abstraction;

interface BackwardTraversable
{
    /**
     * @return BackwardTraversable
     */
    public function prev();
}
