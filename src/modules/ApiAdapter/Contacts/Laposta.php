<?php

namespace ApiAdapter\Contacts;

use ApiAdapter\Contacts\Abstraction\ContactsAdapterInterface;
use ApiAdapter\Contacts\Abstraction\FactoryInterface as ContactsFactoryInterface;
use ApiAdapter\Contacts\Entity\Collection\Contacts;
use ApiAdapter\Contacts\Entity\Collection\Fields;
use ApiAdapter\Contacts\Entity\Collection\Groups;
use ApiAdapter\Contacts\Entity\Contact;
use ApiAdapter\Contacts\Entity\Field;
use ApiAdapter\Contacts\Entity\Group;
use Iterator\Abstraction\FactoryInterface as IteratorFactoryInterface;
use Laposta as LapostaApi;
use Laposta_List;
use Laposta_Member;

class Laposta implements ContactsAdapterInterface
{
    /**
     * @var bool
     */
    private $hasMoreGroups = true;

    /**
     * @var bool
     */
    private $hasMoreContacts = true;

    /**
     * @var ContactsFactoryInterface
     */
    private $factory;

    /**
     * @var IteratorFactoryInterface
     */
    private $iteratorFactory;

    /**
     * Default constructor
     *
     * @param ContactsFactoryInterface $factory
     * @param IteratorFactoryInterface $iteratorFactory
     */
    function __construct(
        ContactsFactoryInterface $factory,
        IteratorFactoryInterface $iteratorFactory
    ) {
        $this->factory         = $factory;
        $this->iteratorFactory = $iteratorFactory;
    }

    /**
     * Get all groups from the API
     *
     * @return Groups
     */
    public function getGroups()
    {
        $list = new Laposta_List();

        $result = $list->all();

        $this->hasMoreGroups = false;
    }

    /**
     * Get all contacts from the API
     *
     * @return Contacts
     */
    public function getContacts()
    {
        $this->hasMoreContacts = false;
    }

    /**
     * Add a new group
     *
     * @param Group $group
     *
     * @return Group
     */
    public function addGroup(Group $group)
    {
        $list         = new Laposta_List();
        $result       = $list->create(
            array(
                 'name' => $group->title,
            )
        );
        $result       = $this->iteratorFactory->createArrayPathIterator($result);
        $group->lapId = $result['list.list_id'];

        return $group;
    }

    /**
     * Add a new contact
     *
     * @param string  $groupId
     * @param Contact $contact
     *
     * @return Contact
     */
    public function addContact($groupId, Contact $contact)
    {
        $member         = new Laposta_Member($groupId);
        $fields         = $this->denormalizeFields($contact->fields);
        $result         = $member->create(
            array(
                 'ip'            => $_SERVER['SERVER_ADDR'],
                 'email'         => $contact->email,
                 'source_url'    => 'http://google.com',
                 'custom_fields' => $fields,
            )
        );
        $result         = $this->iteratorFactory->createArrayPathIterator($result);
        $contact->lapId = $result['member.member_id'];

        return $contact;
    }

    /**
     * Denormalise the fields data for input to Laposta
     *
     * @param Fields $fields
     *
     * @return array
     */
    protected function denormalizeFields(Fields $fields)
    {
        $result = array();

        /** @var $field Field */
        foreach ($fields as $field) {
            $cleanTag          = trim($field->definition->tag, '{}');
            $result[$cleanTag] = $field->value;
        }

        return $result;
    }

    /**
     * Modify an existing contact
     *
     * @param string  $groupId
     * @param Contact $contact
     *
     * @return Contact
     */
    public function updateContact($groupId, Contact $contact)
    {
    }

    /**
     * Modify an existing group
     *
     * @param Group $group
     *
     * @return Group
     */
    public function updateGroup(Group $group)
    {
    }

    /**
     * Add a field to a group
     *
     * @param string $groupId
     * @param Field  $field
     *
     * @return string
     */
    public function addField($groupId, Field $field)
    {
        $lapField = new \Laposta_Field($groupId);
        $result   = $lapField->create(
            array(
                 'name'         => $field->definition->name,
                 'datatype'     => $field->definition->type,
                 'defaultvalue' => $field->definition->defaultValue,
                 'required'     => $field->definition->required ? 'true' : 'false',
                 'in_form'      => $field->definition->showInForm ? 'true' : 'false',
                 'in_list'      => $field->definition->showInList ? 'true' : 'false',
            )
        );
        $result   = $this->iteratorFactory->createArrayPathIterator($result);

        $field->definition->tag = $result['field.tag'];

        return $result['field.field_id'];
    }

    /**
     * Update a field on a group
     *
     * @param string $groupId
     * @param Field  $field
     *
     * @return string
     */
    public function updateField($groupId, Field $field)
    {
    }

    /**
     * Set the access token for the API
     *
     * @param mixed $token
     *
     * @return $this
     */
    public function setAccessToken($token)
    {
        LapostaApi::setApiKey($token);
    }

    /**
     * Get a single group by its identifier
     *
     * @param string $identifier
     *
     * @return Group
     */
    public function getGroup($identifier)
    {
        return null;
    }

    /**
     * Get a single contact by its identifier
     *
     * @param string $identifier
     *
     * @return Contact
     */
    public function getContact($identifier)
    {
        return null;
    }

    /**
     * Indicates whether more groups are available for successive calls to getGroups
     *
     * @return bool
     */
    public function hasMoreGroups()
    {
        return $this->hasMoreGroups;
    }

    /**
     * Indicates whether more contacts are available for successive calls to getContacts
     *
     * @return bool
     */
    public function hasMoreContacts()
    {
        return $this->hasMoreContacts;
    }

    public function removeLists()
    {
        $list   = new Laposta_List();
        $result = $list->all();

        foreach ($result['data'] as $item) {
            $list->delete($item['list']['list_id']);
        };
    }
}
