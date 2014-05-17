<?php

namespace Connect\Command\Sync;

use ApiHelper\Contacts\Entity\Collection\Contacts;
use ApiHelper\Contacts\Entity\Collection\Fields;
use ApiHelper\Contacts\Entity\Collection\Groups;
use ApiHelper\Contacts\Entity\Contact;
use ApiHelper\Contacts\Entity\Field;
use ApiHelper\Contacts\Entity\FieldDefinition;
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
use DateTimeZone;
use Exception;
use Google_Service_Exception;
use Iterator\Abstraction\IteratorFactoryInterface;
use Iterator\LinkedKeyIterator;
use Iterator\MultiLinkedKeyIterator;
use Laposta_Error;
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
     * @var IteratorFactoryInterface
     */
    private $iteratorFactory;

    /**
     * @var LockableInterface
     */
    private $lock;

    /**
     * @param Google                   $google
     * @param Laposta                  $laposta
     * @param Config                   $config
     * @param IteratorFactoryInterface $iteratorFactory
     * @param LockableInterface        $lock
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

        $count = $groups->count();

        if ($count === 0) {
            $this->logger->info('No groups to synchronize');

            return;
        }

        $this->logger->info("Found $count groups.");

        if (!($this->listMap->hooks instanceof MultiLinkedKeyIterator)) {
            $this->listMap->hooks = $this->iteratorFactory->createMultiLinkedKeyIterator();
        }

        $protocol = $this->config->get('ssl') ? 'https' : 'http';
        $hostname = $this->config->get('hostname');
        $hookUrl  = $protocol . '://' . $hostname . '/sync/consume-events/?clientToken=' . $this->clientData->token;

        $this->logger->debug("Existing groups are: " . json_encode($this->listMap->groups->toArray()));

        /** @var $group Group */
        foreach ($groups as $group) {
            $this->logger->debug("Sychronizing group '$group->title'");

            $this->listMap->groupTitles[$group->gId] = $group->title;

            if (preg_match('/^(laposta|mailing)/i', $group->title) !== 1) {
                continue;
            }

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
                $this->listMap->groupElements[$lapId] = new ListMapGroup(array(
                                                                             'fields'   => new MultiLinkedKeyIterator(),
                                                                             'contacts' => new LinkedKeyIterator(),
                                                                         ));
            }
        }
    }

    /**
     * @param Contacts $contacts
     */
    protected function synchronizeContacts(Contacts $contacts)
    {
        $this->logger->info('Synchronizing contacts');

        $count = $contacts->count();

        if ($count === 0) {
            $this->logger->info('No contacts to synchronize');

            return;
        }

        $this->logger->info("Found $count contacts.");

        /** @var $contact Contact */
        foreach ($contacts as $contact) {
            try {
                $this->synchronizeContact($contact);
            }
            catch (Laposta_Error $e) {
                $this->logger->error(
                    "{$e->getMessage()} with code '{$e->getHttpStatus()}'"
                );
            }
            catch (Exception $e) {
                $this->logger->error("{$e->getMessage()} on line '{$e->getLine()}' of '{$e->getFile()}'");
            }
        }
    }

    /**
     * @param Contact $contact
     */
    protected function synchronizeContact(Contact $contact)
    {
        $this->logger->debug("Sychronizing contact '$contact->email'");

        foreach ($this->listMap->groups as $gGroupId) {
            /*
             * Important: Start with an empty lapId
             */
            $contact->lapId = null;
            $lapGroupId     = $this->listMap->groups[$gGroupId];

            $this->logger->debug("Checking contact '$contact->email' in group '$gGroupId'");

            if (empty($lapGroupId)) {
                $this->logger->warning("Unable to import into nonexistent group '$gGroupId'.");

                continue;
            }

            $this->logger->debug(
                "Retrieving group elements for '$lapGroupId' which is '$gGroupId'"
            );

            /** @var $groupElements ListMapGroup */
            $groupElements = $this->listMap->groupElements[$lapGroupId];
            $subscribed    = in_array($gGroupId, $contact->groups->getArrayCopy());

            if (isset($groupElements->contacts[$contact->gId])) {
                $contact->lapId = $groupElements->contacts[$contact->gId];
            }

            if (empty($contact->lapId) && !$subscribed) {
                /*
                 * Contact isn't in the group on either side of the bridge. Safe to skip.
                 */

                $this->logger->debug(
                    "Skipping contact '{$contact->email}' in group '$lapGroupId'"
                );

                continue;
            }

            $this->synchronizeFields($contact->fields, $lapGroupId);
            $this->laposta->setFieldMap($groupElements->fields);

            if (empty($contact->lapId)) {
                $this->logger->debug(
                    "Adding contact '{$contact->email}' in group '$lapGroupId'."
                );

                $this->laposta->addContact($lapGroupId, $contact);

                $this->logger->debug(
                    "Id '{$contact->lapId}' assigned to contact '{$contact->email}'."
                );

                $groupElements->contacts[$contact->lapId] = $contact->gId;
            }
            else {
                $this->logger->debug(
                    "Updating contact '{$contact->email}' in group '$lapGroupId' with id '$contact->lapId'"
                );

                $this->laposta->updateContact($lapGroupId, $contact, $subscribed);
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

            if ($field->definition->type === FieldDefinition::TYPE_SELECT_MULTIPLE || $field->definition->type === FieldDefinition::TYPE_SELECT_SINGLE) {
                $this->logger->debug(
                    "Updating options for field '{$field->definition->name}' in group '$lapGroupId' with id '$lapId'"
                );

                $field->lapId               = $lapId;
                $field->definition->options = $this->resolveGroupName($field->definition->options);
                $field->value               = implode('|', $this->resolveGroupName(explode('|', $field->value)));

                $this->laposta->updateField($lapGroupId, $field);
            }
        }
    }

    /**
     * Convert google group id to a name/title.
     *
     * @param string|array $gGroupId
     *
     * @return string|array
     */
    protected function resolveGroupName($gGroupId)
    {
        if ($gGroupId instanceof \ArrayIterator) {
            $gGroupId = $gGroupId->getArrayCopy();
        }

        if (is_array($gGroupId)) {
            return array_unique(array_map(array($this, 'resolveGroupName'), $gGroupId));
        }

        $result = $this->listMap->groupTitles->primary($gGroupId);

        if (empty($result)) {
            $result = $gGroupId;
        }

        $this->logger->debug(
            "Resolved name for group '{$gGroupId}' to '$result'"
        );

        return $result;
    }

    /**
     * Execute the command
     *
     * @throws \Exception\ExceptionList
     * @return CommandInterface
     */
    public function execute()
    {
        if (!$this->clientData->authGranted) {
            $this->logger->info(
                "Authorization to Google contacts not yet granted for '{$this->clientData->email}'. Skipping import."
            );

            return $this;
        }

        if (!$this->lock->lock($this->clientData->email)) {
            $this->logger->info("Unable to retrieve lock for '{$this->clientData->email}'. Skipping import.");

            return $this;
        }

        $this->clientData->googleTokenSet->refresh_token = $this->clientData->googleRefreshToken;
        $this->clientData->googleTokenSet->fromArray(
            $this->google->setAccessToken($this->clientData->googleTokenSet)
        );

        \Laposta::setApiKey($this->clientData->lapostaApiToken);

        $minDate = new DateTime('@' . $this->clientData->lastImport);
        $this->logger->debug("Setting last google sync time to {$minDate->format('Y-m-d H:i:s T')}");
        $this->google->setDateRange($minDate);
        $this->clientData->lastImport = time();

        while ($this->google->hasMoreGroups()) {
            try {
                $this->synchronizeGroups($this->google->getGroups());
            }
            catch (Laposta_Error $e) {
                $this->logger->error(
                    "{$e->getMessage()} with code '{$e->getHttpStatus()}' and response '{$e->getJsonBody(
                    )}' on line '{$e->getLine()}' of '{$e->getFile()}'"
                );
            }
            catch (Google_Service_Exception $e) {
                $errors = json_encode($e->getErrors());

                $this->logger->error(
                    "{$e->getMessage()} with errors '{$errors}' on line '{$e->getLine()}' of '{$e->getFile()}'"
                );
            }
            catch (Exception $e) {
                $this->logger->error("{$e->getMessage()} on line '{$e->getLine()}' of '{$e->getFile()}'");
            }
        }

        try {
            $this->logger->info('Disabling hooks for all groups');
            $this->laposta->disableHooks($this->listMap->hooks);

            $this->google->setGroupsOptions(array_values($this->listMap->groupTitles->toArray()));

            while ($this->google->hasMoreContacts()) {
                $this->synchronizeContacts($this->google->getContacts());
            }

            $this->logger->info('Re-enabling hooks for all groups');
            $this->laposta->enableHooks($this->listMap->hooks);
        }
        catch (Laposta_Error $e) {
            $this->logger->error(
                "{$e->getMessage()} with code '{$e->getHttpStatus()}'"
                //  and response '{$e->getJsonBody()}' on line '{$e->getLine()}' of '{$e->getFile()}'
            );
        }
        catch (Exception $e) {
            $this->logger->error("{$e->getMessage()} on line '{$e->getLine()}' of '{$e->getFile()}'");
        }

        $this->lock->unlock($this->clientData->email);

        return $this;
    }
}
