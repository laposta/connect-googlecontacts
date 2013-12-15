<?php

namespace GooglePosta\Command\Sync;

use Command\Abstraction\AbstractCommand;
use Command\Abstraction\CommandInterface;
use Entity\Abstraction\SortableInterface;
use Google_Client;
use GooglePosta\Entity\ClientData;
use Laposta;

class SyncGoogle extends AbstractCommand
{
    /**
     * @var Google_Client
     */
    private $client;

    /**
     * @var array
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
     * @param Google_Client $client
     */
    function __construct(Google_Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $apiToken

     *
*@return SyncGoogle
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
     * @param array $listMap

     *
*@return SyncGoogle
     */
    public function setListMap($listMap)
    {
        $this->listMap = $listMap;

        return $this;
    }

    /**
     * @return array
     */
    public function getListMap()
    {
        return $this->listMap;
    }

    /**
     * @param \GooglePosta\Entity\ClientData $clientData
     *
     * @return SyncGoogle
     */
    public function setClientData($clientData)
    {
        $this->clientData = $clientData;

        return $this;
    }

    /**
     * @return \GooglePosta\Entity\ClientData
     */
    public function getClientData()
    {
        return $this->clientData;
    }

    /**
     * Execute the command
     *
     * @return CommandInterface
     */
    public function execute()
    {
        //Laposta::setApiKey($this->apiToken);

        $this->client->setScopes('https://www.google.com/m8/feeds/');
        $this->client->setAccessToken(json_encode($this->clientData->googleTokenSet->toArray()));

        if ($this->client->isAccessTokenExpired()) {
            $this->client->refreshToken($this->clientData->googleRefreshToken);
            $this->clientData->googleTokenSet = json_decode($this->client->getAccessToken(), true);
        }

        $url = 'https://www.google.com/m8/feeds/contacts/default/full?alt=json';


        $request = new \Google_Http_Request($this->client, $url, 'GET', array('GData-Version' => '3.0'));
        $this->client->getAuth()->sign($request);
        $response = $request->execute();


        echo $this->printData($response);
    }

    /**
     * Travers a data structure printing it's contents and path
     *
     * @param mixed  $data
     * @param string $prefix
     * @param bool   $wrap
     *
     * @return string
     */
    public function printData(&$data, $prefix = '', $wrap = true) {
        $out = '';

        if ($wrap === true) {
            $out .= '<pre style="margin: 10px;">';
        }

        if (is_bool($data)) {
            $data = $data ? 'true' : 'false';
            $out .= "<span style=\"color:#090;\">$prefix</span> = <span style=\"color:#909;\">$data</span>\n";
        }
        else if (is_int($data)) {
            $out .= "<span style=\"color:#090;\">$prefix</span> = <span style=\"color:#009;\">$data</span>\n";
        }
        else if (empty($data)) {
            $out .= "<span style=\"color:#090;\">$prefix</span> = <span style=\"color:#999;\">empty</span>\n";
        }
        else if ((!is_array($data) && !($data instanceof \Traversable))) {
            $out .= "<span style=\"color:#090;\">$prefix</span> = <span style=\"color:#900;\">'$data'</span>\n";
        }
        else {
            if ($data instanceof \ArrayIterator) {
                $data->ksort();
            }
            else if ($data instanceof SortableInterface) {
                $data->ksort();
            }
            else if (is_array($data)) {
                ksort($data);
            }

            foreach ($data as $key => $value) {
                $out .= $this->printData($value, trim("$prefix.$key", '.'), false);
            }
        }

        if ($wrap === true) {
            $out .= '</pre>';
        }

        return $out;
    }
}
