<?php

namespace ApiHelper\Contacts\Entity;

use ArrayIterator;
use Entity\Entity;

class Contact extends Entity
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var \ApiHelper\Contacts\Entity\Collection\Fields
     */
    public $fields;

    /**
     * @var ArrayIterator
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
     * @var ArrayIterator
     */
    public $groups;

    /**
     * @var string
     */
    public $lapId;
}
