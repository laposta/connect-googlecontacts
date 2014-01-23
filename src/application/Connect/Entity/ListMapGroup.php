<?php

namespace Connect\Entity;

use Connect\Entity\Abstraction\ClearableEntity;

class ListMapGroup extends ClearableEntity
{
    /**
     * @var \Iterator\LinkedKeyIterator Laposta field id => internal field id
     */
    public $fields;

    /**
     * @var \Iterator\LinkedKeyIterator Laposta contact id => Google contact id
     */
    public $contacts;
}
