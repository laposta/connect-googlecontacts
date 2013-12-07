<?php

namespace GooglePosta\MVC;

use GooglePosta\Command\LoadClientData;
use GooglePosta\Command\StoreClientData;
use GooglePosta\Entity\ClientData;
use GooglePosta\MVC\Base\Controller;
use Web\Exception\RuntimeException;
use Web\Response\Status;

class Authority extends Controller
{
    /**
     * @param string $clientToken
     *
     * @return ClientData
     */
    protected function getClientData($clientToken)
    {
        /** @var $command LoadClientData */
        $command = $this->commandFactory->create('GooglePosta\Command\LoadClientData');
        $command->setClientToken($clientToken)->execute();

        return $command->getClientData();
    }

    /**
     * @param string $clientToken
     * @param ClientData $clientData
     *
     * @return Authority
     */
    protected function storeClientData($clientToken, $clientData)
    {
        /** @var $command StoreClientData */
        $command = $this->commandFactory->create('GooglePosta\Command\StoreClientData');
        $command->setClientToken($clientToken);
        $command->setClientData($clientData);
        $command->execute();

        return $this;
    }

    /**
     * Run the controller
     *
     * @param array $params
     *
     * @throws \Web\Exception\RuntimeException
     */
    public function run($params = array())
    {
        $this->view->setContent(__CLASS__ . "\n Params: " . json_encode($params) . "\n Post: " . json_encode($_POST));

        $email = filter_var($this->request->post('email'), FILTER_VALIDATE_EMAIL);

        if (empty($email)) {
            throw new RuntimeException("Imput not valid. Expected a valid 'email' value");
        }

        $clientData = $this->getClientData(sha1($email));
        $clientData->setEmail($email);
        $clientData->setLapostaApiToken($this->request->post('lapostaApiToken'));

        $this->storeClientData(sha1($email), $clientData);

        pre_dump($clientData);

        $init = $this->commandFactory->create('GooglePosta\Command\InitializeApiBridge');

        $commands = $this->commandFactory->createQueue();

        $this->respond(Status::OK);
    }
}


