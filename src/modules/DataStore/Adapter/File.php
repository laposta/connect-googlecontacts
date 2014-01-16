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
     * @var int
     */
    private $mod;

    /**
     * @param string $path Desired file path
     * @param int    $mod  Desired file mode e.g. 0666 (read and write to everyone)
     */
    function __construct($path, $mod = 0666)
    {
        $this->path = $path;
        $this->mod  = $mod;
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

        try {
            chmod($this->path, $this->mod);
        }
        catch (\Exception $e) {
            // NO-OP
        }

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
