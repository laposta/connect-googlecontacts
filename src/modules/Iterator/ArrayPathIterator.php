<?php

namespace Iterator;

use ArrayAccess;
use InvalidArgumentException;
use Traversable;

/**
 * Class ArrayPathIterator
 * Returns the desired child specified by a.
 * if target is not found null is returned.
 * i.e array_target(array('one'=>array('two'=>array('three'=>1))), "one.two")
 * will return a reference to the value of $array[one][two].
 *
 * @package Iterator
 */
class ArrayPathIterator extends ArrayIterator
{
    /**
     * @var ArrayIterator
     */
    protected $cache = array();

    /**
     * @var string
     */
    protected $delimiter;

    /**
     * @var bool
     */
    protected $isDirty = true;

    /**
     * Constructor override
     *
     * @param array  $array
     * @param int    $flags
     * @param string $delimiter A single character used to delimit the
     *
     * @throws InvalidArgumentException
     */
    public function __construct($array = array(), $flags = 0, $delimiter = '.')
    {
        parent::__construct($array, $flags);

        if (empty($delimiter)) {
            throw new InvalidArgumentException("An empty separator token is not permitted.");
        }

        $this->delimiter = (string) $delimiter;
    }

    /**
     * Build nested array values into path accessible values.
     *
     * @param array|ArrayAccess $array
     * @param mixed             $node
     * @param string            $prefix
     *
     * @throws InvalidArgumentException
     */
    protected function buildCache(&$array, &$node, $prefix = '')
    {
        if (!is_array($array) && !($array instanceof ArrayAccess)) {
            return;
        }

        $array[$prefix] = $node;

        if (!is_array($node) && !($node instanceof Traversable)) {
            return;
        }

        if (!empty($prefix)) {
            $prefix .= $this->delimiter;
        }

        foreach ($node as $key => &$value) {
            $this->buildCache($array, $value, $prefix . $key);
        }
    }

    /**
     * Reset the cache
     */
    protected function resetCache()
    {
        $this->cache = new ArrayIterator();

        $this->buildCache($this->cache, $this);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($index)
    {
        if ($this->isDirty) {
            $this->resetCache();

            $this->isDirty = false;
        }

        if (parent::offsetExists($index)) {
            return true;
        }

        return $this->cache->offsetExists($index);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($index)
    {
        if ($this->isDirty) {
            $this->resetCache();

            $this->isDirty = false;
        }

        if (parent::offsetExists($index)) {
            return parent::offsetGet($index);
        }

        return $this->cache->offsetGet($index);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($index, $newval)
    {
        $targets = explode($this->delimiter, $index);
        $pointer = $this;

        while (count($targets) > 0) {
            $step = array_shift($targets);

            if (!isset($pointer[$step])) {
                $pointer[$step] = array();
            }

            $pointer = & $pointer[$step];
        }

        $pointer = $newval;

        $this->isDirty = true;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($index)
    {
        parent::offsetUnset($index);

        $this->isDirty = true;
    }

    /**
     * @inheritdoc
     */
    public function append($value)
    {
        parent::append($value);

        $this->isDirty = true;
    }

    /**
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        parent::unserialize($serialized);

        $this->isDirty = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        unset($this->cache);

        return parent::serialize();
    }
}
