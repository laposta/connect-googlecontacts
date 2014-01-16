<?php

namespace ApiHelper\Contacts\Entity\Collection;

use ApiHelper\Contacts\Abstraction\AbstractEntityCollection;
use ApiHelper\Contacts\Abstraction\FactoryInterface;
use ApiHelper\Contacts\Entity\Group;
use InvalidArgumentException;

/**
 * Class Groups
 * @method Group current()
 * @method Group next()
 * @method Group offsetGet($index)
 *
 * @package ApiHelper\Contacts\Entity\Collection
 */
class Groups extends AbstractEntityCollection
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
     * @return Group
     */
    protected function ratify($value)
    {
        if ($value instanceof Group) {
            return $value;
        }

        if (!is_traversable($value)) {
            throw new InvalidArgumentException("Unable to convert '$value' to a Group");
        }

        return $this->factory->createGroup($value);
    }
}

