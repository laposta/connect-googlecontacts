<?php

namespace ApiAdapter\Contacts\Entity\Collection;

use ApiAdapter\Contacts\Abstraction\AbstractEntityCollection;
use ApiAdapter\Contacts\Entity\FieldDefinition;
use InvalidArgumentException;

/**
 * Class Contacts
 * @method FieldDefinition current()
 * @method FieldDefinition next()
 * @method FieldDefinition offsetGet($index)
 *
 * @package ApiHelper\Contacts\Entity\Collection
 */
class FieldDefinitions extends AbstractEntityCollection
{
    /**
     * Ensure the value is an Group and if not attempt to convert it.
     *
     * @param mixed $value
     *
     * @throws InvalidArgumentException
     * @return FieldDefinition
     */
    protected function ratify($value)
    {
        if ($value instanceof FieldDefinition) {
            return $value;
        }

        throw new InvalidArgumentException("Expected an object of type 'FieldDefinition'");
    }
}

