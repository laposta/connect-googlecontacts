<?php

namespace ApiHelper\Contacts\Entity;

use Entity\Entity;

class Group extends Entity
{
    /**
     * @var Collection\Fields
     */
    public $fields;

    /**
     * @var string
     */
    public $title;

    /**
     * @var \ArrayIterator
     */
    public $gLinks;

    /**
     * @var string
     */
    public $gEtag;

    /**
     * @var string
     */
    public $gId;

    /**
     * @var string
     */
    public $gUpdated;

    /**
     * @var string
     */
    public $lapId;
}
