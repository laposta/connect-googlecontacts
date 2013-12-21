<?php

namespace ApiAdapter\Contacts\Entity\Factory;

use ApiAdapter\Contacts\Entity\Collection\Fields as FieldsCollection;
use ApiAdapter\Contacts\Entity\Factory\Abstraction\FactoryInterface;
use ApiAdapter\Contacts\Entity\Field;
use ApiAdapter\Contacts\Entity\FieldDefinition;
use Traversable;

class Fields implements FactoryInterface
{
    /**
     * @var FieldsCollection
     */
    private $collectionPrototype;

    /**
     * @var Field
     */
    private $entityPrototype;

    /**
     * @var array
     */
    private $definitionMap = array(
        'birthdate' => array(
            'identifier'   => 'birthdate',
            'name'         => 'Birth Date',
            'type'         => 'date',
            'showInForm'   => true,
            'showInList'   => true,
        ),
        '' => array(
            'identifier'   => '',
            'name'         => '',
            'type'         => '',
            'defaultValue' => '',
            'required'     => '',
            'showInForm'   => '',
            'showInList'   => '',
        ),
        '' => array(
            'identifier'   => '',
            'name'         => '',
            'type'         => '',
            'defaultValue' => '',
            'required'     => '',
            'showInForm'   => '',
            'showInList'   => '',
        ),
        '' => array(
            'identifier'   => '',
            'name'         => '',
            'type'         => '',
            'defaultValue' => '',
            'required'     => '',
            'showInForm'   => '',
            'showInList'   => '',
        ),
        '' => array(
            'identifier'   => '',
            'name'         => '',
            'type'         => '',
            'defaultValue' => '',
            'required'     => '',
            'showInForm'   => '',
            'showInList'   => '',
        ),
    );

    /**
     * @var array
     */
    private $definitionCache;

    /**
     * @param FieldsCollection $collectionPrototype
     * @param Field            $entityPrototype
     */
    function __construct(FieldsCollection $collectionPrototype, Field $entityPrototype)
    {
        $this->collectionPrototype = $collectionPrototype;
        $this->entityPrototype     = $entityPrototype;
    }

    /**
     * Create a new entity collection object with the given data list
     *
     * @param array|Traversable $dateList
     *
     * @return Fields
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
     * @return Field
     */
    public function create($data = array())
    {
        $obj = clone $this->entityPrototype;

        return $obj->fromArray($data);
    }

    /**
     * Get the field definition for the given identifier.
     *
     * @param $identifier
     *
     * @return FieldDefinition
     */
    public function getDefinition($identifier)
    {

    }

    /**
     * Create a field definition object for the given identifier.
     */
    public function createDefinition($identifier)
    {

    }
}
