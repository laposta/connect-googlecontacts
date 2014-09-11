<?php

namespace Connect\MVC\Model;

use ApiHelper\Contacts\Entity\Collection\Groups;
use Command\CommandFactory;
use Connect\Command\ListBuilder\ListContactsForMailingList;
use Connect\Command\ListBuilder\ListMailingListsForClient;
use Connect\Command\LoadClientData;
use Connect\Command\LoadClientMap;
use Connect\Command\StoreClientData;
use Connect\Command\StoreClientMap;
use Connect\Command\Sync\SyncFromGoogle;
use Connect\Entity\ClientData;
use Connect\Entity\ListMap;
use Connect\MVC\Base\Model;
use DirectoryIterator;
use Exception;
use Laposta;
use Laposta_Field;
use Lock\Abstraction\LockableInterface;
use Logger\Abstraction\LoggerInterface;
use RegexIterator;
use RuntimeException;
use SplFileInfo;

class Cli extends Model
{
    /**
     * @var ClientData
     */
    private $clientData;

    /**
     * @var ListMap
     */
    private $clientMap;

    /**
     * @var string
     */
    private $clientToken;

    /**
     * @var LoggerInterface;
     */
    private $logger;

    /**
     * @var LockableInterface
     */
    private $lock;

    public function __construct(CommandFactory $commandFactory, LoggerInterface $logger, LockableInterface $lock)
    {
        parent::__construct($commandFactory);

        $this->logger = $logger;
        $this->lock   = $lock;
    }

    /**
     * Import contacts from google.
     *
     * @param string $dataDir
     *
     * @throws RuntimeException
     * @return $this
     */
    public function importFromGoogle($dataDir)
    {
        if (!file_exists($dataDir) || !is_dir($dataDir)) {
            throw new RuntimeException("Unable to load clients from '$dataDir'. Given path is not a directory.");
        }

        $this->logger->info("Scanning directory '$dataDir' for bridges.");

        $directory = new DirectoryIterator($dataDir);
        $list      = new RegexIterator($directory, '/\.php$/i');

        for ($list->rewind(); $list->valid(); $list->next()) {
            try {
                $this->resetClientProperties();

                /** @var $file SplFileInfo */
                $file              = $list->current();
                $this->clientToken = $file->getBasename('.php');

                $this->logger->info("Commencing import for bridge '$this->clientToken'");

                $this->loadClientData();
                $this->loadClientMap();

                $this->clientData->token = $this->clientToken;

                /** @var $command SyncFromGoogle */
                $command = $this->getCommandFactory()->create('Connect\Command\Sync\SyncFromGoogle');
                $command->setClientData($this->clientData)->setListMap($this->clientMap)->execute();

                $this->persist();
            }
            catch (Exception $e) {
                $this->logger->error("{$e->getMessage()} on line '{$e->getLine()}' of '{$e->getFile()}'");
            }
        }

        return $this;
    }

    /**
     * Create a list of registered client ids.
     *
     * @param string $dataDir
     *
     * @throws RuntimeException
     * @return array
     */
    public function buildClientList($dataDir)
    {
        if (!file_exists($dataDir) || !is_dir($dataDir)) {
            throw new RuntimeException("Unable to load clients from '$dataDir'. Given path is not a directory.");
        }

        $this->logger->info("Scanning directory '$dataDir' for bridges.");

        $directory = new DirectoryIterator($dataDir);
        $list      = new RegexIterator($directory, '/\.php$/i');
        $result    = array();

        for ($list->rewind(); $list->valid(); $list->next()) {
            try {
                /** @var $file SplFileInfo */
                $file     = $list->current();
                $result[] = $file->getBasename('.php');
            }
            catch (Exception $e) {
                $this->logger->error("{$e->getMessage()} on line '{$e->getLine()}' of '{$e->getFile()}'");
            }
        }

        return $result;
    }

    /**
     * Build a list of mailing lists for the given client id
     *
     * @param string $clientId
     *
     * @throws RuntimeException
     * @return Groups
     */
    public function buildMailingListList($clientId)
    {
        $this->resetClientProperties();
        $this->clientToken = $clientId;

        $this->loadClientData();
        $this->loadClientMap();

        $this->clientData->token = $this->clientToken;

        if (empty($this->clientData->lapostaApiToken)) {
            throw new RuntimeException("Client '$clientId' had no API token configured");
        }

        /** @var $command ListMailingListsForClient */
        $command = $this->getCommandFactory()->create('Connect\Command\ListBuilder\ListMailingListsForClient');
        $command->setApiToken($this->clientData->lapostaApiToken)->execute();

        return $command->getMailingLists();
    }

    public function rebuildClientMap($clientId, $fromListId, $toListId)
    {
        $this->resetClientProperties();
        $this->clientToken = $clientId;
        $this->loadClientData();
        $this->loadClientMap();

        $this->clientData->token = $this->clientToken;

        if (empty($this->clientData->lapostaApiToken)) {
            throw new RuntimeException("Client '$clientId' had no API token configured");
        }

        Laposta::setApiKey($this->clientData->lapostaApiToken);

        $groupElements = array();

        /*
         * Webhooks
         */
        $this->clientMap->hooks = array();
        $lapostaHook = new \Laposta_Webhook($toListId);
        $hooksData = $lapostaHook->all();
        foreach ($hooksData['data'] as $hookData) {
            if (strpos($hookData['webhook']['url'], 'connect.laposta.nl') === false) {
                continue;
            }

            $hookId = $hookData['webhook']['webhook_id'];

            $this->clientMap->hooks[$hookId] = $toListId;
        }

        /*
         * Groups
         */
        $this->clientMap->groups[$toListId] = $this->clientMap->groups[$fromListId];
        $this->clientMap->groups->offsetUnset($fromListId);

        /*
         * Fields
         */
        $fields = array_flip($this->clientMap->groupElements[$fromListId]->fields->toArray());

        $lapostaField = new Laposta_Field($fromListId);
        $fieldsData = $lapostaField->all();
        $fromFieldMap = array();
        foreach ($fieldsData['data'] as $fieldData) {
            $fromFieldMap[$fieldData['field']['tag']] = $fieldData['field']['field_id'];
        }

        $lapostaField = new Laposta_Field($toListId);
        $fieldsData = $lapostaField->all();
        foreach ($fieldsData['data'] as $fieldData) {
            $fromFieldId = $fromFieldMap[$fieldData['field']['tag']];
            $field = isset($fields[$fromFieldId]) ? $fields[$fromFieldId] : trim($fieldData['field']['tag'], '{}');
            $groupElements['fields'][$field] = $fieldData['field']['field_id'];

            echo "{$fromFieldId} > {$field} > {$fieldData['field']['field_id']}\n";
        }

        /*
         * Contacts
         */
        $lapostaMember = new \Laposta_Member($fromListId);
        $membersData = $lapostaMember->all();
        $fromMemberMap = array();
        foreach ($membersData['data'] as $memberData) {
            $memberId = $memberData['member']['member_id'];
            $memberEmail = $memberData['member']['email'];
            $fromMemberMap[$memberEmail] = $memberId;
        }

        $lapostaMember = new \Laposta_Member($toListId);
        $membersData = $lapostaMember->all();
        foreach ($membersData['data'] as $memberData) {
            $memberId = $memberData['member']['member_id'];
            $memberEmail = $memberData['member']['email'];

            if (!isset($fromMemberMap[$memberEmail])) {
                continue;
            }

            $groupElements['contacts'][$memberId] = $this->clientMap->groupElements[$fromListId]->contacts[$fromMemberMap[$memberEmail]];

            echo "{$fromMemberMap[$memberEmail]} > {$memberEmail} > {$memberId} => {$groupElements['contacts'][$memberId]}\n";
        }

        $this->clientMap->groupElements[$toListId] = $groupElements;
        $this->clientMap->groupElements->offsetUnset($fromListId);

        $this->clientData->lastImport = null;

        $this->persist();

        echo "DONE!\n";
    }

    protected function resetClientProperties()
    {
        $this->clientData  = null;
        $this->clientMap   = null;
        $this->clientToken = null;
    }

    /**
     * @return Sync
     */
    protected function loadClientData()
    {
        if ($this->clientData instanceof ClientData || empty($this->clientToken)) {
            return $this;
        }

        /** @var $command LoadClientData */
        $command = $this->getCommandFactory()->create('Connect\Command\LoadClientData');
        $command->setClientToken($this->clientToken)->execute();

        $this->clientData = $command->getClientData();

        return $this;
    }

    /**
     * @return Sync
     */
    protected function loadClientMap()
    {
        if (!is_null($this->clientMap) || empty($this->clientToken)) {
            return $this;
        }

        /** @var $command LoadClientMap */
        $command = $this->getCommandFactory()->create('Connect\Command\LoadClientMap');
        $command->setClientToken($this->clientToken)->execute();

        $this->clientMap = $command->getMap();

        return $this;
    }

    /**
     * Persist changes to the model.
     *
     * @return Sync
     */
    protected function persist()
    {
        /** @var $command StoreClientData */
        $command = $this->getCommandFactory()->create('Connect\Command\StoreClientData');
        $command->setClientToken($this->clientToken)->setClientData($this->clientData)->execute();

        /** @var $command StoreClientMap */
        $command = $this->getCommandFactory()->create('Connect\Command\StoreClientMap');
        $command->setClientToken($this->clientToken)->setMap($this->clientMap)->execute();

        return $this;
    }
}
