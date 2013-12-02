<?php

namespace Template\Node;

use Template\Abstraction\NodeInterface;

class Text implements NodeInterface
{
    /**
     * @var string
     */
    protected $content = '';

    /**
     * @param $content
     */
    function __construct($content = '')
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param NodeInterface|string $value
     *
     * @return NodeInterface
     */
    public function addContent($value)
    {
        $this->content .= $value;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->getContent();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
