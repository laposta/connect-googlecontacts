<?php

namespace ApiHelper\Contacts;

use ApiHelper\Contacts\Abstraction\ApiHelperInterface;
use ApiHelper\Contacts\Abstraction\FactoryInterface as ContactsFactoryInterface;
use ApiHelper\Contacts\Entity\Collection\Contacts;
use ApiHelper\Contacts\Entity\Collection\Fields;
use ApiHelper\Contacts\Entity\Collection\Groups;
use ApiHelper\Contacts\Entity\Contact;
use ApiHelper\Contacts\Entity\Field;
use ApiHelper\Contacts\Entity\Group;
use Connect\Entity\GoogleTokenSet;
use DateTime;
use Google_Client;
use Google_Http_Request;
use Google_Http_REST;
use Iterator\Abstraction\IteratorFactoryInterface as IteratorFactoryInterface;
use Iterator\ArrayIterator;
use Logger\Abstraction\LoggerInterface;
use RuntimeException;
use SimpleXMLElement;

class Google implements ApiHelperInterface
{
    const BASE_CONTACT_URL = 'https://www.google.com/m8/feeds/contacts/default/full';

    const BASE_GROUP_URL = 'https://www.google.com/m8/feeds/groups/default/full';

    /**
     * @var Google_Client
     */
    private $client;

    /**
     * @var ContactsFactoryInterface
     */
    private $factory;

    /**
     * @var IteratorFactoryInterface
     */
    private $iteratorFactory;

    /**
     * @var string
     */
    private $groupsUrl = 'https://www.google.com/m8/feeds/groups/default/full?alt=json';

    /**
     * @var string
     */
    private $contactsUrl = 'https://www.google.com/m8/feeds/contacts/default/full?alt=json';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array Mapping of fields to a unique field type identifier
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
        'gContact$website.0.href'                     => 'website',
        'content.$t'                                  => 'notes',
    );

    /**
     * Default constructor
     *
     * @param Google_Client            $client
     * @param ContactsFactoryInterface $factory
     * @param IteratorFactoryInterface $iteratorFactory
     * @param LoggerInterface          $logger
     */
    function __construct(
        Google_Client $client,
        ContactsFactoryInterface $factory,
        IteratorFactoryInterface $iteratorFactory,
        LoggerInterface $logger
    ) {
        $this->client          = $client;
        $this->factory         = $factory;
        $this->iteratorFactory = $iteratorFactory;
        $this->logger          = $logger;
    }

    /**
     * Issue a request
     *
     * @param string $url
     * @param string $method
     * @param array  $headers
     * @param string $postBody
     * @param bool   $json
     *
     * @return mixed
     */
    protected function request($url, $method = 'GET', $headers = array(), $postBody = '', $json = true)
    {
        $headers = array_merge($headers, array('GData-Version' => '3.0'));
        $request = new Google_Http_Request($url, $method, $headers, $postBody);

        $this->logger->info("Sending '$method' request to google with url '$url'");

        $this->client->getAuth()->sign($request);

        if ($json) {
            return Google_Http_REST::execute($this->client, $request);
        }

        return $this->client->getIo()->makeRequest($request)->getResponseBody();
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

        if (!isset($response['feed']) || !is_array($response['feed'])) {
            throw new RuntimeException('Unable to load Groups from google');
        }

        if (!isset($response['feed']['entry']) || !is_array($response['feed']['entry'])) {
            $this->groupsUrl = '';

            return $this->factory->createGroupCollection();
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
        $groups = $this->factory->createGroupCollection();

        foreach ($entries as $entry) {
            $entry = $this->iteratorFactory->createArrayPathIterator($entry);

            $id   = $entry['id.$t'];
            $data = array(
                'title'    => $entry['title.$t'],
                'gLinks'   => $this->normalizeLinks($entry['link']),
                'gEtag'    => $entry['gd$etag'],
                'gId'      => $entry['id.$t'],
                'gUpdated' => $entry['updated.$t'],
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

        if (!isset($response['feed']) || !is_array($response['feed'])) {
            throw new RuntimeException('Unable to load Contacts from google');
        }

        if (!isset($response['feed']['entry']) || !is_array($response['feed']['entry'])) {
            $this->contactsUrl = '';

            return $this->factory->createContactCollection();
        }

        $contacts          = $this->normalizeContacts($response['feed']['entry']);
        $feedLinks         = $this->normalizeLinks($response['feed']['link']);
        $this->contactsUrl = isset($feedLinks['next']) ? $feedLinks['next'] : null;

        return $contacts;
    }

    /**
     * Normalize the google contacts data
     *
     * @param array $entries
     *
     * @return Contacts
     */
    protected function normalizeContacts($entries)
    {
        $contacts = $this->factory->createContactCollection();

        foreach ($entries as $entry) {
            $entry = $this->iteratorFactory->createArrayPathIterator($entry);

            $id   = $entry['id.$t'];
            $data = array(
                'email'      => $this->resolvePrimaryAddress($entry['gd$email']),
                'title'      => $entry['title.$t'],
                'givenName'  => $entry['gd$name.gd$givenName.$t'],
                'familyName' => $entry['gd$name.gd$familyName.$t'],
                'fullName'   => $entry['gd$name.gd$fullName.$t'],
                'fields'     => $this->normalizeFields($entry),
                'gLinks'     => $this->normalizeLinks($entry['link']),
                'gEtag'      => $entry['gd$etag'],
                'gId'        => $entry['id.$t'],
                'gUpdated'   => $entry['updated.$t'],
                'groups'     => $this->normalizeGroupMemberships($entry['gContact$groupMembershipInfo']),
            );

            $contacts[$id] = $data;
        }

        return $contacts;
    }

    /**
     * Normalize fields
     *
     * @param ArrayIterator $entry
     *
     * @return Fields
     */
    protected function normalizeFields($entry)
    {
        $fields = $this->factory->createFieldCollection();

        foreach ($this->fieldMap as $path => $type) {
            if (!isset($entry[$path])) {
                continue;
            }

            $fields[$type] = $this->factory->createField($type, $entry[$path]);
        }

        if (!isset($entry['gContact$userDefinedField'])) {
            return $fields;
        }

        foreach ($entry['gContact$userDefinedField'] as $customField) {
            $type          = $customField['key'];
            $fields[$type] = $this->factory->createField($type, $customField['value']);
        }

        return $fields;
    }

    /**
     * Resolve the group memberships from info list and assign user to groups.
     *
     * @param array $membershipInfoList
     *
     * @return ArrayIterator
     */
    protected function normalizeGroupMemberships($membershipInfoList)
    {
        $groups = $this->iteratorFactory->createArrayIterator();

        if (!is_array($membershipInfoList)) {
            return $groups;
        }

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
     * @return Group
     */
    public function addGroup(Group $group)
    {
        $xmlString = '<atom:entry xmlns:gd="http://schemas.google.com/g/2005">
  <atom:category scheme="http://schemas.google.com/g/2005#kind"
    term="http://schemas.google.com/contact/2008#group"/>
  <atom:title type="text">' . $group->title . '</atom:title>
  <gd:extendedProperty name="source">
    <info>Laposta</info>
  </gd:extendedProperty>
</atom:entry>';

        $group->gId = $this->postGroupXml($xmlString);

        return $group;
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
        // NO-OP
    }

    /**
     * @param string $xmlString
     *
     * @return string The created group id
     */
    protected function postGroupXml($xmlString)
    {
        $headers  = array(
            'Content-Type' => 'application/atom+xml; charset=UTF-8; type=feed',
        );
        $response = $this->request(
            self::BASE_GROUP_URL,
            'POST',
            $headers,
            $xmlString,
            false
        );

        $result = simplexml_load_string($response);

        return (string) $result->id;
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

        $this->groupsUrl   = self::BASE_GROUP_URL . '?alt=json';
        $this->contactsUrl = self::BASE_CONTACT_URL . '?alt=json';

        return $token;
    }

    /**
     * Normalize the links returned by google
     *
     * @param array $links
     *
     * @return ArrayIterator
     */
    protected function normalizeLinks($links)
    {
        $result = $this->iteratorFactory->createArrayIterator();

        if (!is_array($links)) {
            return $result;
        }

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
        if (empty($identifier)) {
            return $this->factory->createGroup();
        }

        return null;
    }

    /**
     * Get a single contact by its identifier
     *
     * @param string $identifier
     *
     * @throws RuntimeException
     * @return Contact
     */
    public function getContact($identifier)
    {
        if (empty($identifier)) {
            return $this->factory->createContact();
        }

        /*
         * Extract the contact id to create the correct url.
         */
        $contactId = substr($identifier, strrpos($identifier, '/'));
        $response  = $this->request(self::BASE_CONTACT_URL . $contactId . '?alt=json');

        if (!isset($response['entry'])) {
            throw new RuntimeException('Unable to load contact from google');
        }

        $entry = $this->iteratorFactory->createArrayPathIterator($response['entry']);
        $data  = array(
            'email'    => $this->resolvePrimaryAddress($entry['gd$email']),
            'fields'   => $this->normalizeFields($entry),
            'gLinks'   => $this->normalizeLinks($entry['link']),
            'gEtag'    => $entry['gd$etag'],
            'gId'      => $entry['id.$t'],
            'gUpdated' => $entry['updated.$t'],
            'groups'   => $this->normalizeGroupMemberships($entry['gContact$groupMembershipInfo']),
        );

        return $this->factory->createContact($data);
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
        $xmlString = "<atom:entry xmlns:atom='http://www.w3.org/2005/Atom' xmlns:gd='http://schemas.google.com/g/2005' xmlns:gContact='http://schemas.google.com/contact/2008'>
            <atom:category scheme='http://schemas.google.com/g/2005#kind' term='http://schemas.google.com/contact/2008#contact'/>
            </atom:entry>";

        $contact->groups = new ArrayIterator(array($groupId));
        $entry           = simplexml_load_string($xmlString);

        $this->applyContactDefinitionToXml($entry, $contact);

        $this->logger->debug("Posting xml to google: {$entry->asXML()}");

        $contact->gId = $this->postContactXml($entry->asXML());

        return $contact;
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
        $contact->groups = new ArrayIterator(array($groupId));
        $entry           = $this->getContactXml($contact->gId);

        $this->applyContactDefinitionToXml($entry, $contact);

        $this->putContactXml($contact->gId, $entry);

        return $contact;
    }

    /**
     * Apply data from contact object to google contacts xml structure.
     *
     * @param SimpleXMLElement $entry
     * @param Contact          $contact
     *
     * @return SimpleXMLElement
     */
    protected function applyContactDefinitionToXml(SimpleXMLElement $entry, Contact $contact)
    {
        $namespaces = $entry->getDocNamespaces(true);
        $gd         = $entry->children($namespaces['gd']);
        $gContact   = $entry->children($namespaces['gContact']);

        /*
         * Update the email address
         */
        if (isset($gd->email) && ($email = $this->filter($gd->email, 'primary', 'true')) !== null) {
            $email->attributes()->address = $contact->email;
        }
        else {
            $email = $entry->addChild('email', null, $namespaces['gd']);
            $email->addAttribute('label', 'Email');
            $email->addAttribute('address', $contact->email);
            $email->addAttribute('primary', 'true');
        }

        /*
         * statically mapped fields
         *
         * 'gContact:birthday.@when'                => 'birth_date',
         * 'gd:name.gd:familyName'                  => 'family_name',
         * 'gd:name.gd:givenName'                   => 'given_name',
         * 'gd:organization.gd:orgName'             => 'organization',
         * 'gd:organization.gd:orgTitle'            => 'position',
         * 'gd:phoneNumber'                         => 'phone',
         * 'gd:structuredPostalAddress.gd:street'   => 'address_street',
         * 'gd:structuredPostalAddress.gd:postcode' => 'address_postcode',
         * 'gd:structuredPostalAddress.gd:city'     => 'address_city',
         * 'gd:structuredPostalAddress.gd:country'  => 'address_country',
         * 'gContact:website.@href'                 => 'website',
         * 'content'                                => 'notes',
         */

        // <gContact:userDefinedField key='Another field' value='with some other information'/>
        // <gContact:userDefinedField key='What the' value='other field'/>

        // <gContact:groupMembershipInfo deleted='false' href='{$groupId}'/>

        /** @var $field Field */
        foreach ($contact->fields as $key => $field) {
            if (empty($field->value)) {
                continue;
            }

            $this->logger->debug("Mapping field '$key' with identifier '{$field->definition->identifier}' for import to google with value '{$field->value}'.");

            switch ($field->definition->identifier) {
                case 'birthdate':
                case 'birth_date':
                    /** @var $birthday SimpleXMLElement */
                    $birthday = $gContact->birthday;

                    if ($birthday->count() === 0) {
                        $birthday = $entry->addChild('birthday', null, $namespaces['gContact']);
                        $birthday->addAttribute('when', '');
                    }

                    $birthday->attributes()->when = $field->value;
                    break;
                case 'surname':
                case 'family_name':
                    if ($gd->name->count() === 0) {
                        $entry->addChild('name', '', $namespaces['gd']);
                    }

                    if ($gd->name->familyName->count() === 0) {
                        $gd->name->addChild('familyName', null, $namespaces['gd']);
                    }

                    $gd->name->familyName = $field->value;

                    unset($gd->name->fullName);
                    break;
                case 'firstname':
                case 'given_name':
                    if ($gd->name->count() === 0) {
                        $entry->addChild('name', '', $namespaces['gd']);
                    }

                    if ($gd->name->givenName->count() === 0) {
                        $gd->name->addChild('givenName', null, $namespaces['gd']);
                    }

                    $gd->name->givenName = $field->value;
                    break;
                case 'organization':
                    if ($gd->organization->count() === 0) {
                        $organization = $entry->addChild('organization', null, $namespaces['gd']);
                        $organization->addAttribute('label', 'Organization');
                    }

                    if ($gd->organization->orgName->count() === 0) {
                        $gd->organization->addChild('orgName', null, $namespaces['gd']);
                    }

                    $gd->organization->orgName = $field->value;
                    break;
                case 'position':
                    if ($gd->organization->count() === 0) {
                        $organization = $entry->addChild('organization', null, $namespaces['gd']);
                        $organization->addAttribute('label', 'Organization');
                    }

                    if ($gd->organization->orgTitle->count() === 0) {
                        $gd->organization->addChild('orgTitle', null, $namespaces['gd']);
                    }

                    $gd->organization->orgTitle = $field->value;
                    break;
                case 'phone':
                    if ($gd->phoneNumber->count() === 0) {
                        $phone = $entry->addChild('phoneNumber', null, $namespaces['gd']);
                        $phone->addAttribute('label', 'Phone');
                    }

                    $gd->phoneNumber = $field->value;
                    break;
                case 'streetaddress':
                case 'address_street':
                    if ($gd->structuredPostalAddress->count() === 0) {
                        $address = $entry->addChild('structuredPostalAddress', null, $namespaces['gd']);
                        $address->addAttribute('label', 'Address');
                    }

                    if ($gd->structuredPostalAddress->street->count() === 0) {
                        $gd->structuredPostalAddress->addChild('street', null, $namespaces['gd']);
                    }

                    $gd->structuredPostalAddress->street = $field->value;

                    unset($gd->structuredPostalAddress->formattedAddress);
                    break;
                case 'postcode':
                case 'address_postcode':
                    if ($gd->structuredPostalAddress->count() === 0) {
                        $address = $gd->addChild('structuredPostalAddress', null, $namespaces['gd']);
                        $address->addAttribute('label', 'Address');;
                    }

                    if ($gd->structuredPostalAddress->postcode->count() === 0) {
                        $gd->structuredPostalAddress->addChild('postcode', null, $namespaces['gd']);
                    }

                    $gd->structuredPostalAddress->postcode = $field->value;

                    unset($gd->structuredPostalAddress->formattedAddress);
                    break;
                case 'city':
                case 'address_city':
                    if ($gd->structuredPostalAddress->count() === 0) {
                        $address = $entry->addChild('structuredPostalAddress', null, $namespaces['gd']);
                        $address->addAttribute('label', 'Address');
                    }

                    if ($gd->structuredPostalAddress->city->count() === 0) {
                        $gd->structuredPostalAddress->addChild('city', null, $namespaces['gd']);
                    }

                    $gd->structuredPostalAddress->city = $field->value;

                    unset($gd->structuredPostalAddress->formattedAddress);
                    break;
                case 'country':
                case 'address_country':
                    if ($gd->structuredPostalAddress->count() === 0) {
                        $address = $entry->addChild('structuredPostalAddress', null, $namespaces['gd']);
                        $address->addAttribute('label', 'Address');
                    }

                    if ($gd->structuredPostalAddress->country->count() === 0) {
                        $gd->structuredPostalAddress->addChild('country', null, $namespaces['gd']);
                    }

                    $gd->structuredPostalAddress->country = $field->value;

                    unset($gd->structuredPostalAddress->formattedAddress);
                    break;
                case 'website':
                    $website = $gContact->website;

                    if ($gContact->website->count() === 0) {
                        $website = $entry->addChild('website', null, $namespaces['gContact']);
                        $website->addAttribute('label', 'Website');
                        $website->addAttribute('href', '');
                    }

                    $website->attributes()->href = $field->value;
                    break;
                case 'notes':
                    if ($entry->content->count() === 0) {
                        $entry->addChild('content', null, isset($namespaces['atom']) ? $namespaces['atom'] : null);
                    }

                    $entry->content = $field->value;
                    break;
                default:
                    $custom = $this->filter($gContact->userDefinedField, 'key', $field->definition->name);

                    if (is_null($custom)) {
                        $custom = $entry->addChild('userDefinedField', null, $namespaces['gContact']);
                        $custom->addAttribute('key', $field->definition->name);
                        $custom->addAttribute('value', '');
                    }

                    $custom->attributes()->value = $field->value;
                    break;
            }
        }

        if (!empty($gd->structuredPostalAddress) && $gd->structuredPostalAddress->count() === 0) {
            $gd->addChild('structuredPostalAddress', null, $namespaces['gd']);
        }

        if (!empty($gd->structuredPostalAddress->formattedAddress) && $gd->structuredPostalAddress->formattedAddress->count() === 0) {
            $gd->structuredPostalAddress->addChild('formattedAddress', null, $namespaces['gd']);
        }

        $gd->structuredPostalAddress->formattedAddress = trim(
            sprintf(
                "%s\n%s %s\n%s",
                $gd->structuredPostalAddress->street,
                $gd->structuredPostalAddress->postcode,
                $gd->structuredPostalAddress->city,
                $gd->structuredPostalAddress->country
            )
        );

        /*
         * Add the group memberships
         */
        foreach ($contact->groups as $groupId) {
            $group = $this->filter($gContact->groupMembershipInfo, 'href', $groupId);

            if (is_null($group)) {
                $group = $entry->addChild('groupMembershipInfo', null, $namespaces['gContact']);
                $group->addAttribute('deleted', 'false');
                $group->addAttribute('href', $groupId);
            }
        }
    }

    /**
     * Filter a collection of xml nodes by attribute value.
     *
     * @param SimpleXMLElement $collection
     * @param string           $key
     * @param string           $value
     *
     * @return SimpleXMLElement
     */
    protected function filter(SimpleXMLElement $collection, $key, $value)
    {
        foreach ($collection as $item) {
            $attributes = $item->attributes();

            if (isset($attributes->$key) && $attributes->$key == $value) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param DateTime $min
     *
     * @return $this
     */
    public function setDateRange(DateTime $min = null)
    {
        $this->groupsUrl   = preg_replace('/updated-min=[^&]*/', '', $this->groupsUrl);
        $this->contactsUrl = preg_replace('/updated-min=[^&]*/', '', $this->contactsUrl);

        if (is_null($min) || $min->getTimestamp() === 0) {
            return;
        }

        $param = 'updated-min=' . $min->format('Y-m-d\TH:i:s');

        if (!empty($this->groupsUrl)) {
            $this->groupsUrl = $this->groupsUrl . (strpos($this->groupsUrl, '?') !== false ? '&' : '?') . $param;
        }

        if (!empty($this->contactsUrl)) {
            $this->contactsUrl = $this->contactsUrl . (strpos($this->contactsUrl, '?') !== false ? '&' : '?') . $param;
        }
    }

    /**
     * @param $contactIdentifier
     * @param $groupIdentifier
     */
    public function removeContactFromGroup($contactIdentifier, $groupIdentifier)
    {
        $contact    = $this->getContactXml($contactIdentifier);
        $namespaces = $contact->getNamespaces(true);

        $index = 0;
        foreach ($contact->children($namespaces['gContact'])->groupMembershipInfo as $group) {
            if ((string) $group->attributes()->href === $groupIdentifier) {
                unset($contact->children($namespaces['gContact'])->groupMembershipInfo[$index]);

                break;
            }

            $index++;
        }

        $this->putContactXml($contactIdentifier, $contact);
    }

    /**
     * @param $contactIdentifier
     * @param $groupIdentifier
     */
    public function addContactToGroup($contactIdentifier, $groupIdentifier)
    {
        $contact    = $this->getContactXml($contactIdentifier);
        $namespaces = $contact->getNamespaces(true);
        $groupNode  = $contact->addChild('groupMembershipInfo', null, $namespaces['gContact']);

        $groupNode->addAttribute('deleted', 'false');
        $groupNode->addAttribute('href', $groupIdentifier);

        $this->putContactXml($contactIdentifier, $contact);
    }

    /**
     * @param string $identifier
     *
     * @return SimpleXMLElement
     */
    protected function getContactXml($identifier)
    {
        $contactId = substr($identifier, strrpos($identifier, '/'));
        $response  = $this->request(self::BASE_CONTACT_URL . $contactId, 'GET', array(), '', false);

        return simplexml_load_string($response);
    }

    /**
     * @param string           $identifier
     * @param SimpleXMLElement $contact
     *
     * @return SimpleXMLElement
     * @throws RuntimeException
     */
    protected function putContactXml($identifier, SimpleXMLElement $contact)
    {
        $contactId  = substr($identifier, strrpos($identifier, '/'));
        $namespaces = $contact->getNamespaces();
        $headers    = array(
            'Content-Type' => 'application/atom+xml; charset=UTF-8; type=feed',
            'If-Match'     => $contact->attributes($namespaces['gd'])->etag,
        );
        $response   = $this->request(
            self::BASE_CONTACT_URL . $contactId,
            'PUT',
            $headers,
            $contact->asXML(),
            false
        );

        if (substr($response, 0, 1) !== '<') {
            throw new RuntimeException("Error PUT-ing contact to Google with response: $response");
        }

        return simplexml_load_string($response);
    }

    /**
     * @param string $xmlString
     *
     * @throws RuntimeException
     * @return string The created contact id
     */
    protected function postContactXml($xmlString)
    {
        $headers  = array(
            'Content-Type' => 'application/atom+xml; charset=UTF-8; type=feed',
        );
        $response = $this->request(
            self::BASE_CONTACT_URL,
            'POST',
            $headers,
            $xmlString,
            false
        );

        if (substr($response, 0, 1) !== '<') {
            throw new RuntimeException("Error POST-ing contact to Google with response: $response");
        }

        $result = simplexml_load_string($response);

        return (string) $result->id;
    }
}
