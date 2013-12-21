<?php

namespace Config;

use InvalidArgumentException;
use RuntimeException;

/**
 * Class Config
 * Decorates the \Iterator\ArrayPathIterator to allow path based config array navigation.
 *
 * @package Config
 */
class Config
{
    /**
     * @var array
     */
    protected $config = array();

    /**
     * @param array $config
     * @param array $supplement
     */
    function __construct(array $config, $supplement = null)
    {
        if (is_array($supplement)) {
            $config = array_replace_recursive($config, $supplement);
        }

        $this->extrapolate($this->config, $config);
    }

    /**
     * Extrapolate nested array values into dot-notation accessible configuration values.
     *
     * @param array  $array
     * @param mixed  $node
     * @param string $prefix
     *
     * @throws InvalidArgumentException
     */
    protected function extrapolate(&$array, &$node, $prefix = '')
    {
        if (!is_array($array)) {
            throw new InvalidArgumentException('Expected first parameter to Config::extrapolate() to be an array.');
        }

        if (!empty($prefix)) {
            $array[$prefix] = $node;
        }

        if (!is_array($node)) {
            return;
        }

        $prefix = ltrim("$prefix.", '.');

        foreach ($node as $key => &$value) {
            $this->extrapolate($array, $value, $prefix . $key);
        }
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
        if (!isset($this->config[$path])) {
            return false;
        }

        return true;
    }

    /**
     * @param string $path
     *
     * @throws RuntimeException
     * @return mixed
     */
    public function get($path)
    {
        if (!isset($this->config[$path])) {
            throw new RuntimeException("Given '$path' could not be found in configuration parameters");
        }

        return $this->config[$path];
    }
}
