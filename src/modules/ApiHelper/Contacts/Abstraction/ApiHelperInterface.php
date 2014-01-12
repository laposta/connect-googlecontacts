<?php

namespace ApiAdapter\Contacts\Abstraction;

use ApiAdapter\Contacts\Entity\Collection\Contacts;
use ApiAdapter\Contacts\Entity\Collection\Groups;
use ApiAdapter\Contacts\Entity\Contact;
use ApiAdapter\Contacts\Entity\Group;
use DateTime;

interface ApiHelperInterface
{
    /**
     * @param DateTime $min
     *
     * @return $this
     */
    public function setDateRange(DateTime $min = null);

    /**
     * Set the access token for the API
     *
     * @param mixed $token
     *
     * @return mixed Possibly updated token
     */
    public function setAccessToken($token);

    /**
     * Get groups from the API
     *
     * @internal param \DateTime $since
     * @return Groups
     */
    public function getGroups();

    /**
     * Indicates whether more groups are available for successive calls to getGroups
     *
     * @return bool
     */
    public function hasMoreGroups();

    /**
     * Get a single group by its identifier
     *
     * @param string $identifier
     *
     * @return Group
     */
    public function getGroup($identifier);

    /**
     * Get contacts from the API
     *
     * @internal param \DateTime $since
     * @return Contacts
     */
    public function getContacts();

    /**
     * Indicates whether more contacts are available for successive calls to getContacts
     *
     * @return bool
     */
    public function hasMoreContacts();

    /**
     * Get a single contact by its identifier
     *
     * @param string $identifier
     *
     * @return Contact
     */
    public function getContact($identifier);

    /**
     * Add a new group
     *
     * @param Group $group
     *
     * @return Group
     */
    public function addGroup(Group $group);

    /**
     * Add a new contact
     *
     * @param string  $groupId
     * @param Contact $contact
     *
     * @return Contact
     */
    public function addContact($groupId, Contact $contact);

    /**
     * Modify an existing contact
     *
     * @param string  $groupId
     * @param Contact $contact
     *
     * @return Contact
     */
    public function updateContact($groupId, Contact $contact);

    /**
     * Modify an existing group
     *
     * @param Group $group
     *
     * @return Group
     */
    public function updateGroup(Group $group);
}
