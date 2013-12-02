<?php

namespace Template\Node\Element;

use Template\Abstraction\AbstractElement;

class Style extends AbstractElement
{
    /**
     * @return string
     */
    protected function getName()
    {
        return 'style';
    }
}
