<?php

namespace DB;

require_once dirname(__FILE__) . '/DBConnectionManager.php';
require_once dirname(__FILE__) . '/DBConnectionConfig.php';

class DB
{
    const DEFAULT_KEY = '(default)';

    /**
     * @var DB
     */
    protected static $instance;

    /**
     * @var DBConnectionManager
     */
    protected $connectionMgr;

    /**
     * @var array
     */
    protected $aliases;

    /**
     * @var PDOStatement
     */
    protected $lastStatement;

    /**
     * @param DBConnectionManager $connectionMgr
     */
    public function __construct(DBConnectionManager $connectionMgr)
    {
        $this->connectionMgr = $connectionMgr;
    }

    /**
     * @return $this
     */
    public function useAsSingletonInstance()
    {
        self::$instance = $this;

        return $this;
    }

    /**
     * @param string $alias
     *
     * @return string
     */
    protected function resolveDatabase($alias)
    {
        if (isset($this->aliases[$alias])) {
            return $this->aliases[$alias];
        }

        return $alias;
    }

    /**
     * @param string $key
     *
     * @return PDO
     */
    public function getConnection($key = self::DEFAULT_KEY)
    {
        return $this->connectionMgr->getConnection(
            $this->resolveDatabase($key)
        );
    }

    /**
     * @param string $sql
     * @param string $method
     *
     * @return string
     */
    protected function annotateQuery($sql, $method = null)
    {
        if (empty($method)) {
            return $sql;
        }

        return '-- ' . $method . PHP_EOL . $sql;
    }

    /**
     * @param PDO    $connection
     * @param string $sql
     *
     * @throws RuntimeException
     * @return PDOStatement
     */
    public function execute(PDO $connection, $sql)
    {
        $e = null;

        try {
            $statement = $connection->query($sql);
        } catch (PDOException $e) {
            $statement = false;
        }

        /*
         * PDO only throws exception when configured to do so.
         * Also check the result to be sure.
         */

        if ($statement === false) {
            throw new RuntimeException('Query failure with SQL: ' . $sql, 0, $e);
        }

        return $this->lastStatement = $statement;
    }

    /**
     * @param string $sql
     * @param string $method
     * @param string $key
     *
     * @return PDOStatement
     */
    public function execQuery($sql, $method = null, $key = self::DEFAULT_KEY)
    {
        return $this->execute(
            $this->getConnection($key),
            $this->annotateQuery($sql, $method)
        );
    }

    /**
     * @param string $sql
     * @param string $method
     * @param string $key
     *
     * @return int
     */
    public function execInsert($sql, $method = null, $key = self::DEFAULT_KEY)
    {
        $connection = $this->getConnection($key);

        $this->execute($connection, $this->annotateQuery($sql, $method));

        return $connection->lastInsertId();
    }

    /**
     * @param string $sql
     * @param string $method
     * @param string $key
     *
     * @throws RuntimeException
     * @return PDOStatement
     */
    public function prep($sql, $method = null, $key = self::DEFAULT_KEY)
    {
        $e = null;

        try {
            $statement = $this->getConnection($key)->prepare(
                $this->annotateQuery($sql, $method)
            );
        } catch (PDOException $e) {
            $statement = false;
        }

        /*
         * PDO only throws exception when configured to do so.
         * Also check the result to be sure.
         */

        if ($statement === false) {
            throw new RuntimeException('Query preparation failure with SQL: ' . $sql, 0, $e);
        }

        return $statement;
    }

    /**
     * @throws RuntimeException
     * @return int
     */
    public function getAffectedCount()
    {
        if (!($this->lastStatement instanceof PDOStatement)) {
            throw new RuntimeException('Unable to retrieve affected rows before a query has been executed');
        }

        return $this->lastStatement->rowCount();
    }

    /**
     * @param string $sql
     * @param string $method
     * @param string $key
     *
     * @return mixed
     */
    public function getRow($sql, $method = null, $key = self::DEFAULT_KEY)
    {
        return $this->execQuery($sql, $method, $key)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $sql
     * @param string $method
     * @param string $key
     *
     * @return mixed
     */
    public function getValue($sql, $method = null, $key = self::DEFAULT_KEY)
    {
        $statement = $this->execQuery($sql, $method, $key);

        $statement->setFetchMode(PDO::FETCH_COLUMN, 0);

        return $statement->fetch();

    }

    /**
     * @param string $sql
     * @param string $method
     * @param string $key
     *
     * @return array
     */
    public function getArray($sql, $method = null, $key = self::DEFAULT_KEY)
    {
        return $this->execQuery($sql, $method, $key)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param        $sql
     * @param null   $method
     * @param string $key
     *
     * @return array
     */
    public function getColumn($sql, $method = null, $key = self::DEFAULT_KEY)
    {
        return $this->execQuery($sql, $method, $key)->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * @param string $string
     * @param string $key
     *
     * @return string
     */
    public function quote($string, $key = self::DEFAULT_KEY)
    {
        return $this->getConnection($key)->quote($string);
    }
}
