<?php

namespace DB;

class DBConnectionConfig
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $database;

    /**
     * @var int
     */
    protected $port = 3306;

    /**
     * @var DBConnectionConfig
     */
    protected $failover;

    /**
     * @param string             $host
     * @param string             $username
     * @param string             $password
     * @param string             $database
     * @param int                $port
     * @param DBConnectionConfig $failover
     */
    function __construct($host, $username, $password, $database, $port = 3306, $failover = null)
    {
        $this->host     = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;

        if ($failover instanceof DBConnectionConfig) {
            $this->failover = $failover;
        }

        if (!empty($port) && is_int($port)) {
            $this->port = $port;
        }
    }

    /**
     * @throws RuntimeException
     * @return string
     */
    public function getDsn()
    {
        $options = array();

        if (empty($this->host)) {
            throw new RuntimeException('Expected host to be non-empty.');
        }

        $options[] = 'host=' . $this->host;

        if (!empty($this->database)) {
            $options[] = 'dbname=' . $this->database;
        }

        if (!empty($this->port) && is_int($this->port)) {
            $options[] = 'port=' . $this->port;
        }

        return 'mysql:' . implode(';', $options);
    }

    /**
     * @param string $database
     *
     * @return $this
     */
    public function setDatabase($database)
    {
        $this->database = $database;

        return $this;
    }

    /**
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param string $host
     *
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param int $port
     *
     * @return $this
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param \DBConnectionConfig $failover
     *
     * @return $this
     */
    public function setFailover($failover)
    {
        if (!is_null($failover) && !($failover instanceof DBConnectionConfig)) {
            return $this;
        }

        $this->failover = $failover;

        return $this;
    }

    /**
     * @return \DBConnectionConfig
     */
    public function getFailover()
    {
        return $this->failover;
    }

}
