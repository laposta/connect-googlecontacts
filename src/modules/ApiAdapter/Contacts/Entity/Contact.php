<?php

namespace ApiAdapter\Contacts\Entity;

use ArrayIterator;
use Entity\Entity;

class Contact extends Entity
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $givenName;

    /**
     * @var string
     */
    public $familyName;

    /**
     * @var string
     */
    public $fullName;

    /**
     * @var \ApiAdapter\Contacts\Entity\Collection\Fields
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
