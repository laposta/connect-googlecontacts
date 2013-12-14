<?php

namespace GooglePosta\Command;

use Command\Abstraction\CommandInterface;
use Config\Config;
use RuntimeException;

class PurgeClientMap implements CommandInterface
{
    /**
     * @var string
     */
    private $clientToken;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config      $config
     */
    function __construct(Config $config)
    {
        $this->config     = $config;
    }

    /**
     * @param string $clientToken
     *
     * @return PurgeClientData
     */
    public function setClientToken($clientToken)
    {
        $this->clientToken = $clientToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientToken()
    {
        return $this->clientToken;
    }

    /**
     * Execute the command
     *
     * @return PurgeClientData
     *
     * @throws \RuntimeException
     */
    public function execute()
    {
        if (empty($this->clientToken)) {
            throw new RuntimeException('Unable to purge client data. A client token is required.');
        }

        $filePath = $this->config->get('path.data') . '/maps/' . $this->clientToken . '.php';

        if (!file_exists($filePath)) {
            return $this;
        }

        unlink($filePath);

        return $this;
    }
}
