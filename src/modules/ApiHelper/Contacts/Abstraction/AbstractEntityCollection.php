<?php

namespace ApiAdapter\Contacts\Abstraction;

use Entity\Entity;
use Entity\Marshal\Typed;
use ReflectionProperty;

abstract class AbstractEntityCollection extends Entity
{
    /**
     * @inheritdoc
     */
    public function set($name, $value)
    {
        return parent::set($name, $this->ratify($value));
    }

    /**
     * Append a value to the collection
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function append($value)
    {
        $this[] = $this->ratify($value);

        return $this;
    }

    /**
     * Ensure the value is of the correct entity type and if not attempt to convert it.
     *
     * @param mixed $value
     *
     * @return $this
     */
    abstract protected function ratify($value);

    /**
     * @inheritdoc
     */
    protected function defaultMarshal()
    {
        return new Typed();
    }

    /**
     * @inheritdoc
     */
    protected function propertiesFilter()
    {
        return ReflectionProperty::IS_PUBLIC;
    }
}
