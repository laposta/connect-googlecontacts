<?php

namespace Template\Node\Element;

use Template\Abstraction\AbstractElement;

class Link extends AbstractElement
{
    /**
     * @return string
     */
    protected function getName()
    {
        return 'link';
    }
}
