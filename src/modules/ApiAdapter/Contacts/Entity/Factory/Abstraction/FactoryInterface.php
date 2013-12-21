<?php

namespace ApiAdapter\Contacts\Entity\Factory\Abstraction;

use ApiAdapter\Contacts\Entity\Collection\Abstraction\AbstractEntityCollection;
use Entity\Entity;
use Traversable;

interface FactoryInterface
{
    /**
     * Create a new entity collection object with the given data list
     *
     * @param array|Traversable $dateList
     *
     * @return AbstractEntityCollection
     */
    public function createCollection($dateList = array());

    /**
     * Create a new entity object with the given data
     *
     * @param array|Traversable $data
     *
     * @return Entity
     */
    public function create($data = array());
} 
