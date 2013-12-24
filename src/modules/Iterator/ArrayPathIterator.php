<?php

namespace Iterator;

use ArrayAccess;
use InvalidArgumentException;
use Traversable;

/**
 * Class ArrayPathIterator
 * <p>Allows navigation of multidimensional arrays using a path as a key.</p>
 * <code>
 *  $a = new ArrayPathIterator(
 *      array(
 *          'one' => array(
 *              'two' => array(
 *                  'three' => '123',
 *               ),
 *          ),
 *      )
 *  );
 *  echo $a['one.two.three']; // 123
 * </code>
 *
 * @package Iterator
 */
class ArrayPathIterator extends ArrayIterator
{
    /**
     * @var ArrayIterator
     */
    protected $cache;

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

        if (!$this->offsetExists($index)) {
            return null;
        }

        return $this->cache->offsetGet($index);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($index, $newval)
    {
        $targets       = explode($this->delimiter, $index);
        $key           = array_shift($targets);
        $this->isDirty = true;

        if (empty($targets)) {
            parent::offsetSet($key, $newval);

            return;
        }

        $source  = array();

        if (parent::offsetExists($key)) {
            $source = parent::offsetGet($key);
        }

        $pointer = & $source;

        while (count($targets) > 0) {
            $step = array_shift($targets);

            if (!isset($pointer[$step])) {
                $pointer[$step] = array();
            }

            $pointer = & $pointer[$step];
        }

        $pointer    = $newval;
        $this[$key] = $source;
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
