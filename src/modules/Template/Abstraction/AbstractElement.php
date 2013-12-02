<?php

namespace Template\Abstraction;

use Template\Node\Attribute;
use Template\Node\Collection\AttributeList;
use Template\Node\Collection\NodeList;
use Template\Node\Text;

abstract class AbstractElement implements NodeInterface
{
    /**
     * @var array
     */
    private $selfClosing = array(
        'area'     => true,
        'base'     => true,
        'basefont' => true,
        'br'       => true,
        'col'      => true,
        'frame'    => true,
        'hr'       => true,
        'img'      => true,
        'input'    => true,
        'link'     => true,
        'meta'     => true,
        'param'    => true,
    );

    /**
     * @var NodeList
     */
    protected $content;

    /**
     * @var AttributeList
     */
    protected $attributes;

    /**
     * @param AttributeList $attributesPrototype
     * @param NodeList      $contentPrototype
     */
    function __construct(AttributeList $attributesPrototype, NodeList $contentPrototype)
    {
        $this->attributes = clone $attributesPrototype;
        $this->content    = clone $contentPrototype;
    }

    /**
     * @return string
     */
    abstract protected function getName();

    /**
     * @return AttributeList Associative array of attributeNames=>attributeValue
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return AbstractElement
     */
    public function addAttribute($name, $value = '')
    {
        $this->attributes->push(new Attribute($name, $value));

        return $this;
    }

    /**
     * @return NodeList Collection of strings or AbstractPrintable objects
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param NodeInterface|string $value
     *
     * @return AbstractElement
     */
    public function addContent($value)
    {
        if (!($value instanceof NodeInterface)) {
            $value = new Text($value);
        }

        $this->content->push($value);

        return $this;
    }

    /**
     * @return string
     */
    public function toString()
    {
        $name  = strtolower($this->getName());
        $open  = trim($name . ' ' . $this->getAttributes());
        $close = '';

        if (isset($this->selfClosing[$name]) && $this->selfClosing[$name] === true) {
            $open .= ' /';
        }
        else {
            $close = $this->getContent() . "\n" . '</' . $name . '>' . "\n";
        }

        return '<' . $open . '>' . "\n" . $close;
    }

    /**
     *
     */
    public function __clone()
    {
        $this->content    = clone $this->content;
        $this->attributes = clone $this->attributes;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}

/*

 */
