<?php

namespace ApiAdapter\Contacts\Entity\Collection;

use ApiAdapter\Contacts\Entity\Collection\Abstraction\AbstractEntityCollection;
use ApiAdapter\Contacts\Entity\Contact;
use InvalidArgumentException;

/**
 * Class Contacts
 * @method Contact current()
 * @method Contact next()
 * @method Contact offsetGet($index)
 *
 * @package ApiAdapter\Contacts\Entity\Collection
 */
class Contacts extends AbstractEntityCollection
{
    /**
     * @var Contact
     */
    private $contactPrototype;

    /**
     * Constructor override
     */
    public function __construct(Contact $contactPrototype)
    {
        parent::__construct();

        $this->contactPrototype = $contactPrototype;
    }

    /**
     * Ensure the value is an Group and if not attempt to convert it.
     *
     * @param mixed $value
     *
     * @throws InvalidArgumentException
     * @return Contact
     */
    protected function ratify($value)
    {
        if ($value instanceof Contact) {
            return $value;
        }

        if (!is_traversable($value)) {
            throw new InvalidArgumentException("Unable to convert '$value' to a Contact");
        }

        $contact = clone $this->contactPrototype;

        return $contact->fromArray($value);
    }
}

