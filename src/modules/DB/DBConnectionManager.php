<?php

namespace DB;

class DBConnectionManager
{
    const CONNECTION_ATTEMPT_LIMIT = 10;

    /**
     * @var array
     */
    protected $configs = array();

    /**
     * @var array
     */
    protected $connections = array();

    /**
     * @var string
     */
    protected $scope = 'default';

    /**
     * @param string $scope
     */
    function __construct($scope = 'default')
    {
        $this->scope = $scope;
    }

    /**
     * Add a database connection config.
     *
     * Multiple configurations for the same database and scope are
     * chained to form a failover flow. When a connection to the first
     * in the list chain fails then the next in the chain will be used.
     *
     * @param DBConnectionConfig $config
     * @param string             $scope
     *
     * @return $this
     */
    public function addConfig(DBConnectionConfig $config, $scope = 'default')
    {
        if (!isset($this->configs[$scope])) {
            $this->configs[$scope] = array();
        }

        $database = $config->getDatabase();

        if (!isset($this->configs[$scope][$database])) {
            $this->configs[$scope][$database] = $config;

            return $this;
        }

        /*
         * if one already exists in the array then we chain them.
         */

        /** @var $existing DBConnectionConfig */
        $existing = $this->configs[$scope][$database];

        while (($next = $existing->getFailover())) {
            $existing = $next;
        }

        $existing->setFailover($config);

        return $this;
    }

    /**
     * Add configs by passing in an array instead of individual calls to DBConnectionManager::addConfig().
     *
     * @param DBConnectionConfig[] $configs
     * @param string               $scope
     *
     * @return $this
     */
    public function addConfigs($configs, $scope = 'default')
    {
        if (!is_array($configs)) {
            return $this;
        }

        foreach ($configs as $config) {
            if (!($config instanceof DBConnectionConfig)) {
                continue;
            }

            $this->addConfig($config, $scope);
        }

        return $this;
    }

    /**
     * @param string $scope
     *
     * @return $this
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * @param string $database Database name
     *
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @return PDO
     */
    public function getConnection($database)
    {
        if (!empty($this->connections[$database])) {
            return $this->connections[$database];
        }

        if (!isset($this->configs[$this->scope][$database])) {
            throw new InvalidArgumentException("Unable to find a connection configuration for '$database'");
        }

        $attempts   = 0;
        $connection = false;
        $config     = $this->configs[$this->scope][$database];

        /** @var $config DBConnectionConfig */

        while ($config instanceof DBConnectionConfig && $attempts < self::CONNECTION_ATTEMPT_LIMIT) {
            $connection = $this->createConnection($config);

            $attempts++;

            if ($connection !== false) {
                break;
            }

            $config = $config->getFailover();
        }

        if ($connection === false) {
            throw new RuntimeException("Connection to '$database' could not be established");
        }

        return $this->connections[$database] = $connection;
    }

    /**
     * @param DBConnectionConfig $config
     *
     * @return PDO
     */
    protected function createConnection(DBConnectionConfig $config)
    {
        $connection = false;

        try {
            $connection = new PDO(
                $config->getDsn(),
                $config->getUsername(),
                $config->getPassword(),
                array(
                    PDO::ATTR_TIMEOUT          => "1",
                    PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                )
            );
        } catch (PDOException $e) {
        }

        return $connection;
    }

}
