<?php

namespace Template\Node;

use Template\Abstraction\NodeInterface;

class Attribute implements NodeInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $content;

    /**
     * @param $name
     * @param $content
     */
    function __construct($name, $content = '')
    {
        $this->name    = $name;
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
     * @param string $value
     *
     * @return Attribute
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
        $result  = $this->getName();
        $content = $this->getContent();

        if (!empty($content)) {
            $result .= '="' . htmlspecialchars($content) . '"';
        }

        return $result;
    }

    /**
     * @param string $name
     *
     * @return Attribute
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
