<?php

namespace GooglePosta\Entity\Collection;

use ApiAdapter\Contacts\Abstraction\AbstractEntityCollection;
use GooglePosta\Entity\ListMapGroup;
use InvalidArgumentException;

/**
 * Class Groups
 * @method ListMapGroup current()
 * @method ListMapGroup next()
 * @method ListMapGroup offsetGet($index)
 *
 * @package ApiHelper\Contacts\Entity\Collection
 */
class ListMapGroups extends AbstractEntityCollection
{
    /**
     * Ensure the value is an Group and if not attempt to convert it.
     *
     * @param mixed $value
     *
     * @throws InvalidArgumentException
     * @return ListMapGroup
     */
    protected function ratify($value)
    {
        if ($value instanceof ListMapGroup) {
            return $value;
        }

        if (!is_traversable($value)) {
            throw new InvalidArgumentException("Unable to convert '$value' to a Group");
        }

        $group = new ListMapGroup($value);

        return $group;
    }
}
