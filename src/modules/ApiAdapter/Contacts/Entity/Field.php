<?php

namespace ApiAdapter\Contacts\Entity;

use Entity\Entity;

class Field extends Entity
{
    /**
     * @var \ApiAdapter\Contacts\Entity\FieldDefinition
     */
    public $definition;

    /**
     * @var string
     */
    public $value;
} 
