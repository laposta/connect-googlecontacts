<?php

namespace Template\Node\Element;

use Template\Abstraction\AbstractElement;

class Script extends AbstractElement
{
    /**
     * @return string
     */
    protected function getName()
    {
        return 'script';
    }
}
