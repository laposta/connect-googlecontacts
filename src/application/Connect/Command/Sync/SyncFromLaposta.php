<?php

namespace Connect\Command\Sync;

use ApiHelper\Contacts\Entity\Collection\Contacts;
use ApiHelper\Contacts\Entity\Collection\Fields;
use ApiHelper\Contacts\Entity\Collection\Groups;
use ApiHelper\Contacts\Entity\Contact;
use ApiHelper\Contacts\Entity\Field;
use ApiHelper\Contacts\Entity\Group;
use ApiHelper\Contacts\Google;
use ApiHelper\Contacts\Laposta;
use Command\Abstraction\AbstractCommand;
use Command\Abstraction\CommandInterface;
use Config\Config;
use Connect\Entity\ClientData;
use Connect\Entity\ListMap;
use Connect\Entity\ListMapGroup;
use Exception;
use Exception\ExceptionList;
use Iterator\Abstraction\IteratorFactoryInterface;
use Iterator\ArrayPathIterator;
use Iterator\LinkedKeyIterator;
use Iterator\MultiLinkedKeyIterator;
use Lock\Abstraction\LockableInterface;
use RuntimeException;

class SyncFromLaposta extends AbstractCommand
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Google
     */
    private $google;

    /**
     * @var Laposta
     */
    private $laposta;

    /**
     * @var ListMap
     */
    private $listMap;

    /**
     * @var string
     */
    private $apiToken;

    /**
     * @var ClientData
     */
    private $clientData;

    /**
     * @var IteratorFactoryInterface
     */
    private $iteratorFactory;

    /**
     * @var array
     */
    private $eventList;

    /**
     * @var LockableInterface
     */
    private $lock;

    /**
     * @param Google            $google
     * @param Laposta           $laposta
     * @param Config            $config
     * @param IteratorFactoryInterface  $iteratorFactory
     * @param LockableInterface $lock
     */
    function __construct(
        Google $google,
        Laposta $laposta,
        Config $config,
        IteratorFactoryInterface $iteratorFactory,
        LockableInterface $lock
    ) {
        $this->config          = $config;
        $this->google          = $google;
        $this->laposta         = $laposta;
        $this->iteratorFactory = $iteratorFactory;
        $this->lock            = $lock;
    }

    /**
     * @param string $apiToken
     *
     * @return SyncFromLaposta
     */
    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * @param ListMap $listMap
     *
     * @return SyncFromLaposta
     */
    public function setListMap(ListMap $listMap)
    {
        $this->listMap = $listMap;

        return $this;
    }

    /**
     * @return ListMap
     */
    public function getListMap()
    {
        return $this->listMap;
    }

    /**
     * @param \Connect\Entity\ClientData $clientData
     *
     * @return SyncFromLaposta
     */
    public function setClientData($clientData)
    {
        $this->clientData = $clientData;

        return $this;
    }

    /**
     * @return \Connect\Entity\ClientData
     */
    public function getClientData()
    {
        return $this->clientData;
    }

    /**
     * @param array $eventList
     *
     * @return SyncFromLaposta
     */
    public function setEventList($eventList)
    {
        $this->eventList = $eventList;

        return $this;
    }

    /**
     * @return array
     */
    public function getEventList()
    {
        return $this->eventList;
    }

    /**
     * @param Groups $groups
     */
    protected function synchronizeGroups(Groups $groups)
    {
        $this->logger->info('Synchronizing groups');

        if ($groups->count() === 0) {
            $this->logger->info('No groups to synchronize');
        }

        if (!($this->listMap->hooks instanceof MultiLinkedKeyIterator)) {
            $this->listMap->hooks = $this->iteratorFactory->createMultiLinkedKeyIterator();
        }

        $protocol = $this->config->get('ssl') ? 'https' : 'http';
        $hostname = $this->config->get('hostname');
        $hookUrl  = $protocol . '://' . $hostname . '/consume-events/?clientToken=' . $this->clientData->token;

        /** @var $group Group */
        foreach ($groups as $group) {
            $lapId = null;

            if (isset($this->listMap->groups[$group->gId])) {
                $lapId = $this->listMap->groups[$group->gId];
            }

            if (empty($lapId)) {
                $this->laposta->addGroup($group);
                $this->laposta->addHooks($group, $hookUrl, $this->listMap->hooks);

                $this->logger->debug("Added new group '$group->title' with id '$group->lapId'");

                $lapId = $group->lapId;
            }
            else {
                $group->lapId = $lapId;

                $this->laposta->updateGroup($group);

                $this->logger->debug("Updated group '$group->title' with id '$group->lapId'");
            }

            $this->logger->debug("Group '{title}' with '{lapId}' is '{gId}'", $group);

            $this->listMap->groups[$lapId] = $group->gId;

            if (!isset($this->listMap->groupElements[$lapId])) {
                $this->listMap->groupElements[$lapId] = new ListMapGroup(
                    array(
                         'fields'   => new LinkedKeyIterator(),
                         'contacts' => new LinkedKeyIterator(),
                    )
                );
            }
        }
    }

    /**
     * @param Contacts $contacts
     */
    protected function synchronizeContacts(Contacts $contacts)
    {
        $this->logger->info('Synchronizing contacts');

        if ($contacts->count() === 0) {
            $this->logger->info('No contacts to synchronize');
        }

        /** @var $contact Contact */
        foreach ($contacts as $contact) {
            $this->synchronizeContact($contact);
        }
    }

    /**
     * @param Contact $contact
     */
    protected function synchronizeContact(Contact $contact)
    {
        foreach ($contact->groups as $gGroupId) {
            $lapGroupId = $this->listMap->groups[$gGroupId];

            if (empty($lapGroupId)) {
                $this->logger->warning("Unable to import into nonexistent group '$gGroupId'.");

                continue;
            }

            $this->synchronizeFields($contact->fields, $lapGroupId);

            /** @var $groupElements ListMapGroup */
            $groupElements = $this->listMap->groupElements[$lapGroupId];

            $lapContactId = null;

            if (isset($groupElements->contacts[$contact->gId])) {
                $lapContactId = $groupElements->contacts[$contact->gId];
            }

            if (empty($lapContactId)) {
                $this->laposta->addContact($lapGroupId, $contact);

                $lapContactId = $contact->lapId;

                $this->logger->debug(
                    "Added new contact '{$contact->email}' in group '$lapGroupId'. Field id is '$lapContactId'"
                );

                $groupElements->contacts[$contact->lapId] = $contact->gId;
            }
            else {
                $this->logger->debug(
                    "Skipping contact '{$contact->email}' in group '$lapGroupId' with id '$lapContactId'"
                );
            }
        }
    }

    /**
     * @param Fields $fields
     * @param string $lapGroupId
     */
    protected function synchronizeFields($fields, $lapGroupId)
    {
        /** @var $groupElements ListMapGroup */
        $groupElements = $this->listMap->groupElements[$lapGroupId];

        /** @var $field Field */
        foreach ($fields as $field) {
            $lapId = null;

            if (isset($groupElements->fields[$field->definition->identifier])) {
                $lapId = $groupElements->fields[$field->definition->identifier];
            }

            if (empty($lapId)) {
                $lapId = $this->laposta->addField($lapGroupId, $field);

                $this->logger->debug(
                    "Added new field '{$field->definition->name}' in group '$lapGroupId'. Field id is '$lapId'"
                );

                $groupElements->fields[$lapId] = $field->definition->identifier;
            }
            else {
                $this->logger->debug(
                    "Skipping field '{$field->definition->name}' in group '$lapGroupId' with id '$lapId'"
                );
            }
        }
    }

    /**
     * @param array $event
     */
    protected function consumeEvent($event)
    {
        if (empty($event['type']) || $event['type'] !== 'member') {
            return;
        }

        $event = new ArrayPathIterator($event);

        $listId   = $event['data.list_id'];
        $memberId = $event['data.member_id'];

        if (!isset($this->listMap->groups[$listId])) {
            /*
             * Ignore groups not originating from google contacts.
             */
            $this->logger->notice("Unrecognized group '$listId'. Skipping event.");

            return;

            /*
             * Or create it at your own risk.
             *
            $group = $this->laposta->getGroup($listId);
            $this->google->addGroup($group);
            $this->listMap->groups[$listId] = $group->gId;
            */
        }

        $groupId   = $this->listMap->groups[$listId];

        if (!isset($this->listMap->groupElements[$listId]->contacts[$memberId])) {
            $contact = $this->laposta->convertToContact($event['data'], $this->listMap->groupElements[$listId]->fields);
            $this->google->addContact($groupId, $contact);
            $this->listMap->groupElements[$listId]->contacts[$memberId] = $contact->gId;

            $this->logger->notice("Unrecognized member '$memberId'. Added new member '$contact->gId' to group '$groupId'.");

            return;
        }

        if (isset($this->listMap->groupElements[$listId]) && isset($this->listMap->groupElements[$listId]->contacts[$memberId])) {
            $contactId = $this->listMap->groupElements[$listId]->contacts[$memberId];

            if ($event['event'] === 'subscribed') {
                $this->google->addContactToGroup($contactId, $groupId);

                $this->logger->info("Added member '$contactId' to group '$groupId'.");
            }
            else if ($event['event'] === 'modified') {
                $contact = $this->laposta->convertToContact($event['data'], $this->listMap->groupElements[$listId]->fields);
                $contact->gId = $this->listMap->groupElements[$listId]->contacts[$memberId];
                $this->google->updateContact($groupId, $contact);

                $this->logger->info("Updated member '$contactId'.");
            }
            else if ($event['event'] === 'deactivated') {
                $this->google->removeContactFromGroup($contactId, $groupId);

                $this->logger->info("Removed member '$contactId' from group '$groupId'.");
            }

//            $this->google->getContact($contactId)->dump();
        }
    }

    /**
     * Execute the command
     *
     * @throws \RuntimeException
     * @throws \Exception\ExceptionList
     * @return CommandInterface
     */
    public function execute()
    {
        if (!$this->lock->lock($this->clientData->lapostaApiToken)) {
            $serialized = serialize($this->eventList);

            throw new RuntimeException("Unable to obtain lock for '{$this->clientData->lapostaApiToken}' to handle events '{$serialized}'.");
        }

        $this->clientData->googleTokenSet->refresh_token = $this->clientData->googleRefreshToken;
        $this->clientData->googleTokenSet->fromArray(
            $this->google->setAccessToken($this->clientData->googleTokenSet)
        );

        \Laposta::setApiKey($this->clientData->lapostaApiToken);

        if (!isset($this->eventList['data']) || !is_array($this->eventList['data'])) {
            throw new RuntimeException('No event data provided');
        }

        $exceptionList = new ExceptionList();

        foreach ($this->eventList['data'] as $event) {
            try {
                $this->consumeEvent($event);
            }
            catch (Exception $e) {
                $this->logger->error($e->getMessage());

                $exceptionList->append($e);
            }
        }

        $this->lock->unlock($this->clientData->lapostaApiToken);

        if ($exceptionList->count() > 0) {
            throw $exceptionList;
        }
    }
}
