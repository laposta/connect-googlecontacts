<?php

namespace ApiHelper\Contacts\Entity;

use Entity\Entity;

class FieldDefinition extends Entity
{
    const TYPE_TEXT = 'text';

    const TYPE_NUMERIC = 'numeric';

    const TYPE_DATE = 'date';

    const TYPE_SELECT_SINGLE = 'select_single';

    const TYPE_SELECT_MULTIPLE = 'select_multiple';

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
    public $type = self::TYPE_TEXT;

    /**
     * @var string
     */
    public $defaultValue = '';

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

    /**
     * @var \ArrayIterator
     */
    public $options = array();

    /**
     * @var string
     */
    public $tag;
} 
