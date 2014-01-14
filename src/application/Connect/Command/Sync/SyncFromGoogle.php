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
use DateTime;
use Iterator\Abstraction\FactoryInterface;
use Iterator\LinkedKeyIterator;
use Iterator\MultiLinkedKeyIterator;
use Lock\Abstraction\LockableInterface;

class SyncFromGoogle extends AbstractCommand
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
     * @var FactoryInterface
     */
    private $iteratorFactory;

    /**
     * @var LockableInterface
     */
    private $lock;

    /**
     * @param Google            $google
     * @param Laposta           $laposta
     * @param Config            $config
     * @param FactoryInterface  $iteratorFactory
     * @param LockableInterface $lock
     */
    function __construct(
        Google $google,
        Laposta $laposta,
        Config $config,
        FactoryInterface $iteratorFactory,
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
     * @return SyncFromGoogle
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
     * @return SyncFromGoogle
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
     * @return SyncFromGoogle
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
                         'fields'   => new MultiLinkedKeyIterator(),
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
                    "Added new contact '{$contact->email}' in group '$lapGroupId'. Contact id is '$lapContactId'"
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

                $groupElements->fields[$field->definition->identifier] = $lapId;
                $this->listMap->tags[$field->definition->tag]          = $field->definition->identifier;
            }
            else {
                $this->logger->debug(
                    "Skipping field '{$field->definition->name}' in group '$lapGroupId' with id '$lapId'"
                );
            }
        }
    }

    /**
     * Execute the command
     *
     * @return CommandInterface
     */
    public function execute()
    {
        if ($this->lock->isLocked($this->clientData->lapostaApiToken)) {
            return $this;
        }

        $this->clientData->googleTokenSet->refresh_token = $this->clientData->googleRefreshToken;
        $this->clientData->googleTokenSet->fromArray(
            $this->google->setAccessToken($this->clientData->googleTokenSet)
        );

        \Laposta::setApiKey($this->clientData->lapostaApiToken);

        $minDate = new DateTime();
        $minDate->setTimestamp($this->clientData->lastImport);
        $this->google->setDateRange($minDate);

        $this->clientData->lastImport = time();

        while ($this->google->hasMoreGroups()) {
            $this->synchronizeGroups($this->google->getGroups());
        }

        $this->logger->info('Disabling hooks for all existing groups');
        $this->laposta->disableHooks($this->listMap->hooks);

        while ($this->google->hasMoreContacts()) {
            $this->synchronizeContacts($this->google->getContacts());
        }

        $this->logger->info('Re-enabling hooks for all groups');
        $this->laposta->enableHooks($this->listMap->hooks);

        return $this;
    }
}
