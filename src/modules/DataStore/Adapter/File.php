<?php

namespace DataStore\Adapter;

use DataStore\Adapter\Abstraction\AdapterInterface;

class File implements AdapterInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * @param string $path
     */
    function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Persist the given data
     *
     * @param mixed $data
     *
     * @return AdapterInterface
     */
    public function persist($data)
    {
        file_put_contents($this->path, "<?php\n\nreturn " . var_export($data, true) . ";\n");

        return $this;
    }

    /**
     * Retrieve and return previously persisted data.
     *
     * @return mixed
     */
    public function retrieve()
    {
        if (!file_exists($this->path) || !is_readable($this->path)) {
            return;
        }

        return include "$this->path";
    }
}
