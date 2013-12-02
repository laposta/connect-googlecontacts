<?php

namespace Template\Node\Collection;

use Collection\ListIterator;
use Printable;

class NodeList extends ListIterator implements Printable
{
    /**
     * @return string
     */
    public function toString()
    {
        return implode("", $this->getArrayCopy());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
