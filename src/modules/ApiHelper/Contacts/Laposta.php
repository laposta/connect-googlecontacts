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
use DateTime;
use Iterator\Abstraction\IteratorFactoryInterface as IteratorFactoryInterface;
use Iterator\ArrayIterator;
use Iterator\ArrayPathIterator;
use Iterator\LinkedKeyIterator;
use Iterator\MultiLinkedKeyIterator;
use Laposta as LapostaApi;
use Laposta_List;
use Laposta_Member;
use Laposta_Webhook;

class Laposta implements ApiHelperInterface
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
     * @var ArrayIterator
     */
    private $fieldCache;

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

        $this->fieldCache = new ArrayIterator();
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
        $field->lapId           = $result['field.field_id'];

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
        if (empty($identifier)) {
            return $this->factory->createGroup();
        }

        $list   = new Laposta_List();
        $result = $this->iteratorFactory->createArrayPathIterator($list->get($identifier));
        $group  = $this->factory->createGroup(
            array(
                 'title'  => $result['list.name'],
                 '$lapId' => $result['list.list_id'],
            )
        );

        return $group;
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
        if (empty($identifier)) {
            return $this->factory->createContact();
        }

        return null;
    }

    /**
     * Convert data from the source into a native contact object.
     *
     * @param array             $data
     * @param LinkedKeyIterator $fieldMap
     *
     * @return Contact
     */
    public function convertToContact(array $data, LinkedKeyIterator $fieldMap)
    {
        /*
         * $data = {
                "member_id": "5t8zgm63qk",
                "list_id": "v5hcnwzyqo",
                "email": "angus.mcbiefstuk@codeblanche.com",
                "custom_fields": {
					"birthdate": "1982-03-05",
					"surname": "McBiefstuk",
					"firstname": "Angus",
					"organization": "Black Angus",
					"streetaddress": "Grote Boterbloem 41",
					"postcode": "1991LJ",
					"city": "Velserbroek",
					"country": "Netherlands",
					"website": "http://codeblanche.com",
					"notes": "Angus is the best!",
					"anotherfield": "with some other information",
					"whatthe": "other field"
                }
            };
         */

        /** @var $contact Contact */
        $contact  = $this->factory->createContact();
        $iterator = new ArrayPathIterator($data);
        $groupId  = $iterator['list_id'];
        $tagMap   = $this->getFieldsMap($groupId);

        $contact->email = $iterator['email'];
        $contact->lapId = $iterator['member_id'];

        foreach ($iterator['custom_fields'] as $key => $value) {
            $type = $key;

            if (isset($tagMap[$key]) && isset($fieldMap[$tagMap[$key]])) {
                $type = $fieldMap[$tagMap[$key]];
            }

            $contact->fields[$type] = $this->factory->createField($type, $value);
        }

        return $contact;
    }

    /**
     * @param string $groupId
     *
     * @return \Iterator\ArrayIterator
     */
    protected function getFieldsMap($groupId)
    {
        if (isset($this->fieldCache[$groupId])) {
            return $this->fieldCache[$groupId];
        }

        $lapField = new \Laposta_Field($groupId);
        $result   = $lapField->all();
        $fields   = new ArrayIterator();

        foreach ($result['data'] as $item) {
            $key          = trim($item['field']['tag'], '{}');
            $fields[$key] = $item['field']['field_id'];
        };

        return $this->fieldCache[$groupId] = $fields;
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

    /**
     * Remove all lists.
     */
    public function removeLists()
    {
        $list   = new Laposta_List();
        $result = $list->all();

        foreach ($result['data'] as $item) {
            $list->delete($item['list']['list_id']);
        };
    }

    /**
     * @param DateTime $min
     *
     * @return $this
     */
    public function setDateRange(DateTime $min = null)
    {
        // NO-OP
    }

    /**
     * @param Group                  $group
     * @param string                 $callbackUrl
     * @param MultiLinkedKeyIterator $hooks
     *
     * @return $this
     */
    public function addHooks(Group $group, $callbackUrl, MultiLinkedKeyIterator $hooks)
    {
        if (empty($callbackUrl)) {
            return $this;
        }

        $events = array(
            'subscribed',
            'modified',
            'deactivated',
        );

        foreach ($events as $event) {
            $hook = new Laposta_Webhook($group->lapId);

            $result = $hook->create(
                array(
                     'event'   => $event,
                     'url'     => $callbackUrl,
                     'blocked' => 'false',
                )
            );

            $result = $this->iteratorFactory->createArrayPathIterator($result);

            $hooks[$result['webhook.webhook_id']] = $group->lapId;
        }

        return $this;
    }

    /**
     * Temporarily disable the webhooks.
     *
     * @param MultiLinkedKeyIterator $hooks
     *
     * @return $this
     */
    public function disableHooks(MultiLinkedKeyIterator $hooks)
    {
        foreach ($hooks as $hookId => $groupId) {
            $hook = new Laposta_Webhook($groupId);

            $hook->update($hookId, array('blocked' => 'true'));
        }
    }

    /**
     * Re-enable temporarily disabled webhooks.
     *
     * @param MultiLinkedKeyIterator $hooks
     *
     * @return $this
     */
    public function enableHooks(MultiLinkedKeyIterator $hooks)
    {
        foreach ($hooks as $hookId => $groupId) {
            $hook = new Laposta_Webhook($groupId);

            $hook->update($hookId, array('blocked' => 'false'));
        }
    }
}
