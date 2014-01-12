<?php

namespace ApiAdapter\Contacts\Abstraction;

use ApiAdapter\Contacts\Entity\FieldDefinition;
use Entity\Entity;
use Traversable;

interface FactoryInterface
{
    /**
     * Create a new contact entity collection with the given data list
     *
     * @param array|Traversable $list
     *
     * @return AbstractEntityCollection
     */
    public function createContactCollection($list = array());

    /**
     * Create a new field entity collection with the given data list
     *
     * @param array|Traversable $list
     *
     * @return AbstractEntityCollection
     */
    public function createFieldCollection($list = array());

    /**
     * Create a new group entity collection with the given data list
     *
     * @param array|Traversable $list
     *
     * @return AbstractEntityCollection
     */
    public function createGroupCollection($list = array());

    /**
     * Create a new field definition entity collection with the given definitions list
     *
     * @param FieldDefinition[] $list
     *
     * @return AbstractEntityCollection
     */
    public function createFieldDefinitionCollection($list = array());

    /**
     * Create a new contact entity with the given data
     *
     * @param array|Traversable $data
     *
     * @return Entity
     */
    public function createContact($data = array());

    /**
     * Create a new field entity with the given data
     *
     * @param string $type
     * @param string $value
     *
     * @return Entity
     */
    public function createField($type, $value);

    /**
     * Create a new group entity with the given data
     *
     * @param array|Traversable $data
     *
     * @return Entity
     */
    public function createGroup($data = array());

    /**
     * Create a new field definition entity with the given data
     *
     * @param string $type
     *
     * @return Entity
     */
    public function createFieldDefinition($type);
} 
