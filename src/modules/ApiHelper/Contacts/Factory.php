<?php

namespace ApiAdapter\Contacts;

use ApiAdapter\Contacts\Abstraction\FactoryInterface;
use ApiAdapter\Contacts\Entity\Collection\Contacts;
use ApiAdapter\Contacts\Entity\Collection\FieldDefinitions;
use ApiAdapter\Contacts\Entity\Collection\Fields;
use ApiAdapter\Contacts\Entity\Collection\Groups;
use ApiAdapter\Contacts\Entity\Contact;
use ApiAdapter\Contacts\Entity\Field;
use ApiAdapter\Contacts\Entity\FieldDefinition;
use ApiAdapter\Contacts\Entity\Group;

class Factory implements FactoryInterface
{

    /**
     * @var array Mapping of the unique field type identifier to its corresponding definition metadata.
     */
    private $fieldDefinitionMetadata = array(
        'birth_date'       => array(
            'name'       => 'Birth Date',
            'showInForm' => true,
            'showInList' => true,
        ),
        'family_name'      => array(
            'name'       => 'Surname',
            'showInForm' => true,
            'showInList' => true,
        ),
        'given_name'       => array(
            'name'       => 'First Name',
            'showInForm' => true,
            'showInList' => true,
        ),
        'organization'     => array(
            'name'       => 'Organization',
            'showInForm' => true,
            'showInList' => true,
        ),
        'position'         => array(
            'name'       => 'Job Title',
            'showInForm' => true,
            'showInList' => true,
        ),
        'phone'            => array(
            'name'       => 'Phone',
            'showInForm' => true,
            'showInList' => true,
        ),
        'address_street'   => array(
            'name'       => 'Street Address',
            'showInForm' => true,
            'showInList' => true,
        ),
        'address_postcode' => array(
            'name'       => 'Postcode',
            'showInForm' => true,
            'showInList' => true,
        ),
        'address_city'     => array(
            'name'       => 'City',
            'showInForm' => true,
            'showInList' => true,
        ),
        'address_country'  => array(
            'name'       => 'Country',
            'showInForm' => true,
            'showInList' => true,
        ),
        'website'          => array(
            'name'       => 'Website',
            'showInForm' => true,
            'showInList' => true,
        ),
        'notes'            => array(
            'name' => 'Notes',
        ),
    );

    /**
     * @var array Cache of previously created field definitions
     */
    private $fieldDefinitionCache = array();

    /**
     * @inheritdoc
     */
    public function createContactCollection($list = array())
    {
        $collection = new Contacts($this);

        return $collection->fromArray($list);
    }

    /**
     * @inheritdoc
     */
    public function createFieldCollection($list = array())
    {
        $collection = new Fields($this);

        return $collection->fromArray($list);
    }

    /**
     * @inheritdoc
     */
    public function createGroupCollection($list = array())
    {
        $collection = new Groups($this);

        return $collection->fromArray($list);
    }

    /**
     * @inheritdoc
     */
    public function createContact($data = array())
    {
        return new Contact($data);
    }

    /**
     * @inheritdoc
     */
    public function createField($type, $value)
    {
        $field = new Field(
            array(
                 'definition' => $this->createFieldDefinition($type),
                 'value'      => $value,
            )
        );

        return $field;
    }

    /**
     * @inheritdoc
     */
    public function createGroup($data = array())
    {
        return new Group($data);
    }

    /**
     * @inheritdoc
     */
    public function createFieldDefinition($type)
    {
        if (isset($this->fieldDefinitionCache[$type])) {
            return $this->fieldDefinitionCache[$type];
        }

        $metadata = array('name' => $type);

        if (isset($this->fieldDefinitionMetadata[$type])) {
            $metadata = $this->fieldDefinitionMetadata[$type];
        }

        $definition             = new FieldDefinition($metadata);
        $definition->identifier = $type;

        return $this->fieldDefinitionCache[$type] = $definition;
    }

    /**
     * @inheritdoc
     */
    public function createFieldDefinitionCollection($list = array())
    {
        return new FieldDefinitions($list);
    }
}
