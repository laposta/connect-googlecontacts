<?php

namespace GooglePosta\Entity;

use Entity\Entity;

class ListMap extends Entity
{
    /**
     * @var \Iterator\LinkedKeyIterator
     */
    public $groups;

    /**
     * @var \Iterator\LinkedKeyIterator
     */
    public $fields;

    /**
     * @var \Iterator\LinkedMultiKeyIterator
     */
    public $contacts;


} 
