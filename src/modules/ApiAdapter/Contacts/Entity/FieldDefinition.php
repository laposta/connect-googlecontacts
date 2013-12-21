<?php

namespace ApiAdapter\Contacts\Entity;

use Entity\Entity;

class FieldDefinition extends Entity
{
    const TYPE_TEXT = 'text';

    const TYPE_NUMERIC = 'numeric';

    const TYPE_DATE = 'date';

    /**
     * @var string
     */
    public $identifier;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $defaultValue;

    /**
     * @var bool
     */
    public $required = false;

    /**
     * @var bool
     */
    public $showInForm = false;

    /**
     * @var bool
     */
    public $showInList = false;
} 
