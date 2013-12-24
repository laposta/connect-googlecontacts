<?php

namespace Config;

use Iterator\ArrayPathIterator;
use RuntimeException;

/**
 * Class Config
 * <p>Decorates the \Iterator\ArrayPathIterator to allow path based config array navigation.</p>
 *
 * @package Config
 */
class Config
{
    /**
     * @var ArrayPathIterator
     */
    protected $iterator = array();

    /**
     * @param ArrayPathIterator $iterator
     * @param array             $config
     * @param array             $override
     */
    function __construct(ArrayPathIterator $iterator, array $config, $override = null)
    {
        if (is_array($override)) {
            $config = array_replace_recursive($config, $override);
        }

        $this->iterator = $iterator;

        $this->iterator->fromArray($config);
    }

    /**
     * Check for the existence of a configuration value by key.
     *
     * @param $path
     *
     * @return bool
     */
    public function has($path)
    {
        return isset($this->iterator[$path]);
    }

    /**
     * @param string $path
     *
     * @throws RuntimeException
     * @return mixed
     */
    public function get($path)
    {
        if (!$this->has($path)) {
            throw new RuntimeException("Given '$path' could not be found in configuration parameters");
        }

        return $this->iterator[$path];
    }

    /**
     * @param string $path
     * @param mixed  $value
     *
     * @return $this
     */
    public function set($path, $value)
    {
        $this->iterator[$path] = $value;

        return $this;
    }
}
