<?php

namespace ApiAdapter\Contacts\Entity\Factory;

use ApiAdapter\Contacts\Entity\Collection\Groups as GroupsCollection;
use ApiAdapter\Contacts\Entity\Factory\Abstraction\FactoryInterface;
use ApiAdapter\Contacts\Entity\Group;
use Traversable;

class Groups implements FactoryInterface
{
    /**
     * @var GroupsCollection
     */
    private $collectionPrototype;

    /**
     * @var Group
     */
    private $entityPrototype;

    /**
     * @param GroupsCollection $collectionPrototype
     * @param Group            $entityPrototype
     */
    function __construct(GroupsCollection $collectionPrototype, Group $entityPrototype)
    {
        $this->collectionPrototype = $collectionPrototype;
        $this->entityPrototype     = $entityPrototype;
    }

    /**
     * Create a new entity collection object with the given data list
     *
     * @param array|Traversable $dateList
     *
     * @return Groups
     */
    public function createCollection($dateList = array())
    {
        $obj = clone $this->collectionPrototype;

        return $obj->fromArray($dateList);
    }

    /**
     * Create a new entity object with the given data
     *
     * @param array|Traversable $data
     *
     * @return Group
     */
    public function create($data = array())
    {
        $obj = clone $this->entityPrototype;

        return $obj->fromArray($data);
    }
}
