<?php

namespace Template\Node\Collection;

class AttributeList extends NodeList
{
    /**
     * @return string
     */
    public function toString()
    {
        return implode(' ', $this->getArrayCopy());
    }
}
