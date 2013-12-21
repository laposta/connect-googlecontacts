<?php

namespace ApiAdapter\Contacts\Entity\Collection;

use ApiAdapter\Contacts\Entity\Collection\Abstraction\AbstractEntityCollection;
use ApiAdapter\Contacts\Entity\Group;
use InvalidArgumentException;

/**
 * Class Groups
 * @method Group current()
 * @method Group next()
 * @method Group offsetGet($index)
 *
 * @package ApiAdapter\Contacts\Entity\Collection
 */
class Groups extends AbstractEntityCollection
{
    /**
     * @var Group
     */
    private $groupPrototype;

    /**
     * Constructor override
     *
     * @param Group $groupPrototype
     */
    public function __construct(Group $groupPrototype)
    {
        parent::__construct();

        $this->groupPrototype = $groupPrototype;
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

        $group = clone $this->groupPrototype;

        return $group->fromArray($value);
    }
}

