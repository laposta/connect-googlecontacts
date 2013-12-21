<?php

namespace ApiAdapter\Contacts;

use ApiAdapter\Contacts\Abstraction\ContactsAdapterInterface;
use ApiAdapter\Contacts\Entity\Collection\Contacts;
use ApiAdapter\Contacts\Entity\Collection\Groups;
use ApiAdapter\Contacts\Entity\Contact;
use ApiAdapter\Contacts\Entity\Group;
use Laposta as LapostaApi;
use Laposta_List;

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
     * @return bool
     */
    public function addGroup(Group $group)
    {
    }

    /**
     * Add a new contact
     *
     * @param Group   $group
     * @param Contact $contact
     *
     * @return bool
     */
    public function addContact(Group $group, Contact $contact)
    {
    }

    /**
     * Modify an existing contact
     *
     * @param Group   $group
     * @param Contact $contact
     *
     * @return bool
     */
    public function updateContact(Group $group, Contact $contact)
    {
    }

    /**
     * Modify an existing group
     *
     * @param Group $group
     *
     * @return bool
     */
    public function updateGroup(Group $group)
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
}
