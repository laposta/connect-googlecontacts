<?php

namespace ApiHelper\Contacts\Entity;

use Entity\Entity;

class Field extends Entity
{
    /**
     * @var \ApiHelper\Contacts\Entity\FieldDefinition
     */
    public $definition;

    /**
     * @var string
     */
    public $value;

    /**
     * @var string
     */
    public $lapId;
} 
