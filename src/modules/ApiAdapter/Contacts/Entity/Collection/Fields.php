<?php

namespace ApiAdapter\Contacts\Entity\Collection;

use ApiAdapter\Contacts\Entity\Collection\Abstraction\AbstractEntityCollection;
use ApiAdapter\Contacts\Entity\Field;
use InvalidArgumentException;

/**
 * Class Contacts
 * @method Field current()
 * @method Field next()
 * @method Field offsetGet($index)
 *
 * @package ApiAdapter\Contacts\Entity\Collection
 */
class Fields extends AbstractEntityCollection
{
    /**
     * @var Field
     */
    private $fieldPrototype;

    /**
     * Constructor override
     */
    public function __construct(Field $fieldPrototype)
    {
        parent::__construct();

        $this->fieldPrototype = $fieldPrototype;
    }

    /**
     * Ensure the value is an Group and if not attempt to convert it.
     *
     * @param mixed $value
     *
     * @throws InvalidArgumentException
     * @return Field
     */
    protected function ratify($value)
    {
        if ($value instanceof Field) {
            return $value;
        }

        if (!is_traversable($value)) {
            throw new InvalidArgumentException("Unable to convert '$value' to a Field");
        }

        $field = clone $this->fieldPrototype;

        return $field->fromArray($value);
    }
}

