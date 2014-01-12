<?php

namespace ApiAdapter\Contacts\Entity\Collection;

use ApiAdapter\Contacts\Abstraction\AbstractEntityCollection;
use ApiAdapter\Contacts\Abstraction\FactoryInterface;
use ApiAdapter\Contacts\Entity\Contact;
use InvalidArgumentException;

/**
 * Class Contacts
 * @method Contact current()
 * @method Contact next()
 * @method Contact offsetGet($index)
 *
 * @package ApiHelper\Contacts\Entity\Collection
 */
class Contacts extends AbstractEntityCollection
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * Constructor override
     *
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        parent::__construct();

        $this->factory = $factory;
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
            throw new InvalidArgumentException("Unable to convert '$value' to a Group");
        }

        return $this->factory->createContact($value);
    }
}

