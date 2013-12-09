<?php

namespace MVC;

use Entity\Entity;
use ReflectionProperty;

abstract class Model extends Entity
{
    /**
     * @inheritdoc
     */
    protected function propertiesFilter()
    {
        return ReflectionProperty::IS_PUBLIC;
    }

    /**
     * Persist changes to the model.
     *
     * @return Model
     */
    abstract public function persist();
}
