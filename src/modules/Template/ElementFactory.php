<?php

namespace Template;

use RuntimeException;
use Template\Abstraction\AbstractElement;
use Template\Abstraction\ElementFactoryInterface;
use Template\Node\Collection\AttributeList;
use Template\Node\Collection\NodeList;

class ElementFactory implements ElementFactoryInterface
{
    /**
     * @var array
     */
    protected $prototypes = array();

    /**
     * @var NodeList
     */
    protected $nodeListPrototype;

    /**
     * @var AttributeList
     */
    protected $attributeListPrototype;

    /**
     * @param AttributeList $attributeListPrototype
     * @param NodeList      $nodeListPrototype
     */
    function __construct(AttributeList $attributeListPrototype, NodeList $nodeListPrototype)
    {
        $this->attributeListPrototype = $attributeListPrototype;
        $this->nodeListPrototype      = $nodeListPrototype;
    }

    /**
     * @param string $className
     *
     * @return AbstractElement
     * @throws \RuntimeException
     */
    public function get($className)
    {
        $nodeSpace   = '\\' . __NAMESPACE__ . '\\Node\\Element';
        $fqClassName = $nodeSpace . '\\' . $className;

        if (!class_exists($fqClassName)) {
            throw new RuntimeException("Unable to find class '$className' in '$nodeSpace'");
        }

        if (!isset($this->prototypes[$fqClassName])) {
            $this->prototypes[$fqClassName] = new $fqClassName(clone $this->attributeListPrototype, clone $this->nodeListPrototype);
        }

        return clone $this->prototypes[$fqClassName];
    }
}
