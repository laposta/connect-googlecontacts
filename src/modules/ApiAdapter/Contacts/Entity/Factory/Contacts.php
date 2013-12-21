<?php

namespace ApiAdapter\Contacts\Entity\Factory;

use ApiAdapter\Contacts\Entity\Collection\Contacts as ContactsCollection;
use ApiAdapter\Contacts\Entity\Contact;
use ApiAdapter\Contacts\Entity\Factory\Abstraction\FactoryInterface;
use Traversable;

class Contacts implements FactoryInterface
{
    /**
     * @var ContactsCollection
     */
    private $collectionPrototype;

    /**
     * @var Contact
     */
    private $entityPrototype;

    /**
     * @param ContactsCollection $collectionPrototype
     * @param Contact            $entityPrototype
     */
    function __construct(ContactsCollection $collectionPrototype, Contact $entityPrototype)
    {
        $this->collectionPrototype = $collectionPrototype;
        $this->entityPrototype     = $entityPrototype;
    }

    /**
     * Create a new entity collection object with the given data list
     *
     * @param array|Traversable $dateList
     *
     * @return ContactsCollection
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
     * @return Contact
     */
    public function create($data = array())
    {
        $obj = clone $this->entityPrototype;

        return $obj->fromArray($data);
    }
}
