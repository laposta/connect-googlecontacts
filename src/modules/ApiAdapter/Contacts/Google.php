<?php

namespace ApiAdapter\Contacts;

use ApiAdapter\Contacts\Abstraction\ContactsAdapterInterface;
use ApiAdapter\Contacts\Entity\Collection\Contacts;
use ApiAdapter\Contacts\Entity\Collection\Groups;
use ApiAdapter\Contacts\Entity\Contact;
use ApiAdapter\Contacts\Entity\Factory\Contacts as ContactsFactory;
use ApiAdapter\Contacts\Entity\Factory\Fields as FieldsFactory;
use ApiAdapter\Contacts\Entity\Factory\Groups as GroupsFactory;
use ApiAdapter\Contacts\Entity\Group;
use ArrayIterator;
use Google_Client;
use Google_Http_Request;
use GooglePosta\Entity\GoogleTokenSet;
use RuntimeException;

class Google implements ContactsAdapterInterface
{
    /**
     * @var \Google_Client
     */
    private $client;

    /**
     * @var GroupsFactory
     */
    private $groupsFactory;

    /**
     * @var ContactsFactory
     */
    private $contactsFactory;

    /**
     * @var FieldsFactory
     */
    private $fieldsFactory;

    /**
     * @var string
     */
    private $groupsUrl;

    /**
     * @var string
     */
    private $contactsUrl;

    /**
     * @var array Mapping of fields to FieldsFactory->definitionsMap
     */
    private $fieldMap = array(
        'gContact$birthday.when'                      => 'birth_date',
        'gd$name.gd$familyName.$t'                    => 'family_name',
        'gd$name.gd$givenName.$t'                     => 'given_name',
        'gd$organization.0.gd$orgName.$t'             => 'organization',
        'gd$organization.0.gd$orgTitle.$t'            => 'position',
        'gd$phoneNumber.0.$t'                         => 'phone',
        'gd$structuredPostalAddress.0.gd$street.$t'   => 'address_street',
        'gd$structuredPostalAddress.0.gd$postcode.$t' => 'address_postcode',
        'gd$structuredPostalAddress.0.gd$city.$t'     => 'address_city',
        'gd$structuredPostalAddress.0.gd$country.$t'  => 'address_country',
        ''                                            => '',
        'content.$t'                                  => 'notes',
    );

    /*
gContact$userDefinedField.0.key = 'Another field'
gContact$userDefinedField.0.value = 'with some other information'
gContact$userDefinedField.1.key = 'What the'
gContact$userDefinedField.1.value = 'other field'

gContact$website.0.href = 'http://codeblanche.com'
gContact$website.0.rel = 'home-page'
gd$email.0.address = 'angus@codeblanche.com'
gd$email.0.primary = 'true'
gd$email.0.rel = 'http://schemas.google.com/g/2005#work'
gd$email.1.address = 'angus.mcbiefstuk@codeblanche.com'
gd$email.1.rel = 'http://schemas.google.com/g/2005#home'
gd$etag = '"RXYzezVSLit7I2A9Wh5UFUoKQAU."'
gd$name.gd$familyName.$t = 'McBiefstuk'
gd$name.gd$fullName.$t = 'Angus McBiefstuk'
gd$name.gd$givenName.$t = 'Angus'
gd$organization.0.gd$orgName.$t = 'Black Angus'
gd$organization.0.gd$orgTitle.$t = 'Butcher'
gd$organization.0.rel = 'http://schemas.google.com/g/2005#other'
gd$structuredPostalAddress.0.gd$city.$t = 'Velserbroek'
gd$structuredPostalAddress.0.gd$country.$t = 'Netherlands'
gd$structuredPostalAddress.0.gd$country.code = 'NL'
gd$structuredPostalAddress.0.gd$formattedAddress.$t = 'Grote Boterbloem 41
1991LJ Velserbroek
Netherlands'
gd$structuredPostalAddress.0.gd$postcode.$t = '1991LJ'
gd$structuredPostalAddress.0.gd$street.$t = 'Grote Boterbloem 41'
gd$structuredPostalAddress.0.rel = 'http://schemas.google.com/g/2005#work'
     */

    /**
     * Default constructor
     *
     * @param Google_Client   $client
     * @param GroupsFactory   $groupsFactory
     * @param ContactsFactory $contactsFactory
     * @param FieldsFactory   $fieldsFactory
     */
    function __construct(
        Google_Client $client,
        GroupsFactory $groupsFactory,
        ContactsFactory $contactsFactory,
        FieldsFactory $fieldsFactory
    ) {
        $this->client          = $client;
        $this->groupsFactory   = $groupsFactory;
        $this->contactsFactory = $contactsFactory;
        $this->fieldsFactory   = $fieldsFactory;
        $this->groupsUrl       = 'https://www.google.com/m8/feeds/groups/default/full?alt=json';
        $this->contactsUrl     = 'https://www.google.com/m8/feeds/contacts/default/full?alt=json';
    }

    /**
     * Issue a request
     *
     * @param string $url
     * @param string $method
     * @param array  $headers
     *
     * @return array
     */
    protected function request($url, $method = 'GET', $headers = array())
    {
        $headers = array_merge($headers, array('GData-Version' => '3.0'));
        $request = new Google_Http_Request($this->client, $url, $method, $headers);

        $this->client->getAuth()->sign($request);

        return $request->execute();
    }

    /**
     * Get all groups from the API
     *
     * @throws RuntimeException
     * @return Groups
     */
    public function getGroups()
    {
        if (!$this->hasMoreGroups()) {
            return null;
        }

        $response = $this->request($this->groupsUrl);

        if (!isset($response['feed']['entry']) || !is_array($response['feed']['entry'])) {
            throw new RuntimeException('Unable to load Groups from google');
        }

        $groups          = $this->normalizeGroups($response['feed']['entry']);
        $feedLinks       = $this->normalizeLinks($response['feed']['link']);
        $this->groupsUrl = isset($feedLinks['next']) ? $feedLinks['next'] : null;

        return $groups;
    }

    /**
     * Normalize the google groups data
     *
     * @param array $entries
     *
     * @return Groups
     */
    protected function normalizeGroups($entries)
    {
        $groups = $this->groupsFactory->createCollection();

        foreach ($entries as $entry) {
            $id   = $entry['id']['$t'];
            $data = array(
                'title'    => array_target($entry, 'title.$t'),
                'gLinks'   => new ArrayIterator($this->normalizeLinks(array_target($entry, 'link'))),
                'gEtag'    => array_target($entry, 'gd$etag'),
                'gId'      => array_target($entry, 'id.$t'),
                'gUpdated' => array_target($entry, 'updated.$t'),
            );

            $groups[$id] = $data;
        }

        return $groups;
    }

    /**
     * Get all contacts from the API
     *
     * @throws RuntimeException
     * @return Contacts
     */
    public function getContacts()
    {
        if (!$this->hasMoreContacts()) {
            return null;
        }

        $response = $this->request($this->contactsUrl);

        if (!isset($response['feed']['entry']) || !is_array($response['feed']['entry'])) {
            throw new RuntimeException('Unable to load Contacts from google');
        }

        $contacts          = $this->normalizeContacts($response['feed']['entry']);
        $feedLinks         = $this->normalizeLinks($response['feed']['link']);
        $this->contactsUrl = isset($feedLinks['next']) ? $feedLinks['next'] : null;

        $contacts->dump();

        return $contacts;
    }

    /**
     * Normalize the google contacts data
     *
     * @param $entries
     *
     * @return Contacts
     */
    protected function normalizeContacts($entries)
    {
        $contacts = $this->contactsFactory->createCollection();

        foreach ($entries as $entry) {
            $id     = array_target($entry, 'id.$t');
            $groups = $this->normalizeGroupMemberships(array_target($entry, 'gContact$groupMembershipInfo'));
            $data   = array(
                'title'      => array_target($entry, 'title.$t'),
                'email'      => $this->resolvePrimaryAddress(array_target($entry, 'gd$email')),
                'givenName'  => array_target($entry, 'gd$name.gd$givenName.$t'),
                'familyName' => array_target($entry, 'gd$name.gd$familyName.$t'),
                'fullName'   => array_target($entry, 'gd$name.gd$fullName.$t'),
                'fields'     => new ArrayIterator($this->normalizeFields($entry)),
                'gLinks'     => new ArrayIterator($this->normalizeLinks(array_target($entry, 'link'))),
                'gEtag'      => array_target($entry, 'gd$etag'),
                'gId'        => array_target($entry, 'id.$t'),
                'gUpdated'   => array_target($entry, 'updated.$t'),
                'groups'     => new ArrayIterator($groups),
            );

            $contacts[$id] = $data;
        }

        return $contacts;
    }

    /**
     * Normalize fields
     *
     * @param array $entry
     *
     * @return array
     */
    protected function normalizeFields($entry)
    {
        if ($entry['title']['$t'] === 'Angus McBiefstuk') {
            pretty_dump($entry);
        }

        return array();
    }

    /**
     * Resolve the group memberships from info list and assign user to groups.
     *
     * @param array $membershipInfoList
     *
     * @return array
     */
    protected function normalizeGroupMemberships($membershipInfoList)
    {
        if (!is_array($membershipInfoList)) {
            return array();
        }

        $groups = array();

        foreach ($membershipInfoList as $membershipInfo) {
            if ($membershipInfo['deleted'] === 'true') {
                continue;
            }

            $groups[] = $membershipInfo['href'];
        }

        return $groups;
    }

    /**
     * Resolve the primary email address from a list
     *
     * @param array $addresses
     *
     * @return string
     */
    protected function resolvePrimaryAddress($addresses)
    {
        if (!is_array($addresses)) {
            return '';
        }

        $primary = '';

        foreach ($addresses as $address) {
            if (isset($address['primary']) && $address['primary'] !== 'true') {
                continue;
            }

            $primary = $address['address'];
        }

        return $primary;
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
        // not implemented
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
     * @return mixed Possibly updated token
     */
    public function setAccessToken($token)
    {
        if ($token instanceof GoogleTokenSet) {
            $token = $token->toArray();
        }

        $this->client->setAccessToken(json_encode($token));

        if ($this->client->isAccessTokenExpired() && isset($token['refresh_token'])) {
            $this->client->refreshToken($token['refresh_token']);

            $token = json_decode($this->client->getAccessToken(), true);
        }

        return $token;
    }

    /**
     * Normalize the links returned by google
     *
     * @param array $links
     *
     * @return array
     */
    protected function normalizeLinks($links)
    {
        if (!is_array($links)) {
            return array();
        }

        $result = array();

        foreach ($links as $link) {
            $rel          = $link['rel'];
            $result[$rel] = $link['href'];
        }

        return $result;
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
        return !empty($this->groupsUrl);
    }

    /**
     * Indicates whether more contacts are available for successive calls to getContacts
     *
     * @return bool
     */
    public function hasMoreContacts()
    {
        return !empty($this->contactsUrl);
    }
}
