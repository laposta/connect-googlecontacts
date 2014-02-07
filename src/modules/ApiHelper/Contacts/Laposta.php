<?php

namespace ApiHelper\Contacts;

use ApiHelper\Contacts\Abstraction\ApiHelperInterface;
use ApiHelper\Contacts\Abstraction\FactoryInterface as ContactsFactoryInterface;
use ApiHelper\Contacts\Entity\Collection\Contacts;
use ApiHelper\Contacts\Entity\Collection\Fields;
use ApiHelper\Contacts\Entity\Collection\Groups;
use ApiHelper\Contacts\Entity\Contact;
use ApiHelper\Contacts\Entity\Field;
use ApiHelper\Contacts\Entity\FieldDefinition;
use ApiHelper\Contacts\Entity\Group;
use DateTime;
use Entity\Exception\RuntimeException;
use Iterator\Abstraction\IteratorFactoryInterface as IteratorFactoryInterface;
use Iterator\ArrayIterator;
use Iterator\ArrayPathIterator;
use Iterator\LinkedKeyIterator;
use Iterator\MultiLinkedKeyIterator;
use Laposta as LapostaApi;
use Laposta_List;
use Laposta_Member;
use Laposta_Webhook;
use Logger\Abstraction\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var LinkedKeyIterator
     */
    private $fieldMap;

    /**
     * Default constructor
     *
     * @param ContactsFactoryInterface $factory
     * @param IteratorFactoryInterface $iteratorFactory
     * @param LoggerInterface          $logger
     */
    function __construct(
        ContactsFactoryInterface $factory,
        IteratorFactoryInterface $iteratorFactory,
        LoggerInterface $logger
    ) {
        $this->factory         = $factory;
        $this->iteratorFactory = $iteratorFactory;
        $this->logger          = $logger;

        $this->fieldCache = new ArrayIterator();
    }

    /**
     * Get all groups from the API
     *
     * @return Groups
     */
    public function getGroups()
    {
        //        $list = new Laposta_List();
        //
        //        $result = $list->all();
        //
        //        $this->hasMoreGroups = false;
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
        $member = new Laposta_Member($groupId);
        $fields = $this->denormalizeFields($contact->fields, $groupId);
        $data   = array(
            'ip'            => isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1',
            'email'         => $contact->email,
            'source_url'    => 'http://google.com',
            'custom_fields' => $fields,
        );

        $this->logger->debug(
            "Adding contact '$contact->email' with data: " . json_encode($data)
        );

        $result         = $this->iteratorFactory->createArrayPathIterator(
            $member->create($data)
        );
        $contact->lapId = $result['member.member_id'];

        return $contact;
    }

    /**
     * Denormalise the fields data for input to Laposta
     *
     * @param Fields $fields
     * @param string $groupId
     *
     * @return array
     * @throws RuntimeException
     */
    protected function denormalizeFields(Fields $fields, $groupId)
    {
        if (!($this->fieldMap instanceof LinkedKeyIterator) || $this->fieldMap->count() === 0) {
            throw new RuntimeException('Unable to denormalize fields without the field map.');
        }

        $result = array();
        $tagMap = array_flip($this->getFieldsMap($groupId)->getArrayCopy());

        $this->logger->debug(
            "Resolving fields using tag map: " . json_encode($tagMap)
        );

        /** @var $field Field */
        foreach ($fields as $field) {
            $tag = '';

            if (isset($tagMap[$this->fieldMap[$field->definition->identifier]])) {
                $tag = $tagMap[$this->fieldMap[$field->definition->identifier]];
            }

            $cleanTag          = trim($tag, '{}');
            $value             = $field->value;

            if ($field->definition->type === FieldDefinition::TYPE_SELECT_MULTIPLE) {
                $value = explode('|', $value);
            }

            $result[$cleanTag] = $value;
        }

        return $result;
    }

    /**
     * Update an existing contact
     *
     * @param string  $groupId
     * @param Contact $contact
     * @param bool    $subscribed
     *
     * @return Contact
     */
    public function updateContact($groupId, Contact $contact, $subscribed = true)
    {
        $member = new Laposta_Member($groupId);
        $fields = $this->denormalizeFields($contact->fields, $groupId);
        $data   = array(
            'ip'            => isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1',
            'email'         => $contact->email,
            'state'         => $subscribed ? 'active' : 'unsubscribed',
            'custom_fields' => $fields,
        );

        $this->logger->debug(
            "Updating contact '$contact->email' with data: " . json_encode($data)
        );

        $result         = $member->update(
            $contact->lapId,
            $data
        );
        $result         = $this->iteratorFactory->createArrayPathIterator($result);
        $contact->lapId = $result['member.member_id'];

        return $contact;
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
        if (isset($this->fieldCache[$groupId])) {
            unset($this->fieldCache[$groupId]);
        }

        $lapField = new \Laposta_Field($groupId);
        $meta     = array(
            'name'         => $field->definition->name,
            'datatype'     => $field->definition->type,
            'defaultvalue' => $field->definition->defaultValue,
            'required'     => $field->definition->required ? 'true' : 'false',
            'in_form'      => $field->definition->showInForm ? 'true' : 'false',
            'in_list'      => $field->definition->showInList ? 'true' : 'false',
        );

        if ($field->definition->type === FieldDefinition::TYPE_SELECT_MULTIPLE || $field->definition->type === FieldDefinition::TYPE_SELECT_SINGLE) {
            $meta['options'] = $field->definition->options;
        }

        $result = $lapField->create($meta);
        $result = $this->iteratorFactory->createArrayPathIterator($result);

        $field->definition->tag = $result['field.tag'];
        $field->definition->synchronised = true;
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
        if ($field->definition->synchronised) {
            return $field->lapId;
        }

        if (isset($this->fieldCache[$groupId])) {
            unset($this->fieldCache[$groupId]);
        }

        $lapField = new \Laposta_Field($groupId);
        $meta     = array(
            'name'         => $field->definition->name,
            'datatype'     => $field->definition->type,
            'defaultvalue' => $field->definition->defaultValue,
            'required'     => $field->definition->required ? 'true' : 'false',
            'in_form'      => $field->definition->showInForm ? 'true' : 'false',
            'in_list'      => $field->definition->showInList ? 'true' : 'false',
        );

        if ($field->definition->type === FieldDefinition::TYPE_SELECT_MULTIPLE || $field->definition->type === FieldDefinition::TYPE_SELECT_SINGLE) {
            $meta['options'] = $field->definition->options;
        }

        $lapField->update($field->lapId, $meta);

        $field->definition->synchronised = true;

        return $field->lapId;
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
     * @param array $data
     *
     * @return Contact
     * @throws RuntimeException
     */
    public function convertToContact(array $data)
    {
        if (!($this->fieldMap instanceof LinkedKeyIterator) || $this->fieldMap->count() === 0) {
            throw new RuntimeException('Unable to convert to contact without the field map. Map given was: ' . var_export(
                                           $this->fieldMap,
                                           true
                                       ));
        }

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
                    "groups": ["",""],
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

            if ($type === 'groups') {
                continue;
            }

            if (isset($tagMap[$key]) && isset($this->fieldMap[$tagMap[$key]])) {
                $type = $this->fieldMap[$tagMap[$key]];
            }

            $contact->fields[$type] = $this->factory->createField($type, $value);
        }

        $contact->groups = $iterator['custom_fields.groups'];

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
            $this->logger->debug("Disabling hook '$hookId' for list '$groupId'");

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
            $this->logger->debug("Enabling hook '$hookId' for list '$groupId'");

            $hook = new Laposta_Webhook($groupId);

            $hook->update($hookId, array('blocked' => 'false'));
        }
    }

    /**
     * @param \Iterator\LinkedKeyIterator $fieldMap
     *
     * @return Laposta
     */
    public function setFieldMap($fieldMap)
    {
        $this->fieldMap = $fieldMap;

        return $this;
    }

    /**
     * @return \Iterator\LinkedKeyIterator
     */
    public function getFieldMap()
    {
        return $this->fieldMap;
    }
}
