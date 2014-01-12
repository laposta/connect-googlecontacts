<?php

namespace GooglePosta\Entity;

use Entity\Entity;

class ListMapGroup extends Entity
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
