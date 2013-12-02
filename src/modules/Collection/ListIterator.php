<?php

namespace Collection;

use ArrayAccess;
use Collection\Abstraction\ArrayManageable;
use Collection\Abstraction\BackwardTraversable;
use Collection\Abstraction\Clearable;
use Collection\Abstraction\Insertable;
use Countable;
use SeekableIterator;
use Serializable;

class ListIterator implements ArrayAccess, SeekableIterator, Countable, Serializable, BackwardTraversable, Clearable, ArrayManageable, Insertable
{
    /**
     * @var int
     */
    private $indexCounter = 0;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var array
     */
    private $keys = array();

    /**
     * @var array
     */
    private $values = array();

    /**
     * Default constructor
     *
     * @param array $array
     */
    public function __construct($array = array())
    {
        $this->fromArray((array) $array);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        if (!isset($this->values[$this->position])) {
            return null;
        }

        return $this->values[$this->position];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        if (!isset($this->keys[$this->position])) {
            return null;
        }

        return $this->keys[$this->position];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid()
    {
        return isset($this->values[$this->position]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        $index = array_search($offset, $this->keys);

        return $index !== false;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        $index = array_search($offset, $this->keys);

        return $this->values[$index];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $offset = $this->indexCounter;

            $this->indexCounter++;
        }

        array_push($this->keys, $offset);
        array_push($this->values, $value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        $index = array_search($offset, $this->keys);

        if ($index === false) {
            return;
        }

        unset($this->keys[$index], $this->values[$index]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     *
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize(
            array(
                'indexCounter' => $this->indexCounter,
                'position'     => $this->position,
                'keys'         => $this->keys,
                'data'         => $this->values,
            )
        );
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     *
     * @link http://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
     *
     * @return void
     */
    public function unserialize($serialized)
    {
        $restore = unserialize($serialized);

        $this->indexCounter = $restore['indexCounter'];
        $this->position     = $restore['position'];
        $this->keys         = $restore['keys'];
        $this->values       = $restore['data'];
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *       </p>
     *       <p>
     *       The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->values);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Seeks to a position
     *
     * @link http://php.net/manual/en/seekableiterator.seek.php
     *
     * @param int $position <p>
     *                      The position to seek to.
     *                      </p>
     *
     * @return void
     */
    public function seek($position)
    {
        $this->position = min($this->count(), max(0, $position));
    }

    /**
     * @return ListIterator
     */
    public function prev()
    {
        $this->position--;
    }

    /**
     * @return ListIterator
     */
    public function clear()
    {
        $this->keys         = array();
        $this->values       = array();
        $this->indexCounter = 0;
        $this->position     = 0;
    }

    /**
     * Pop a value off the end of the list.
     *
     * @return ListIterator
     */
    public function pop()
    {
        array_pop($this->keys);

        return array_pop($this->values);
    }

    /**
     * Push a value onto the end of the list.
     *
     * @param mixed $value
     *
     * @return ListIterator
     */
    public function push($value)
    {
        $this->offsetSet(null, $value);
    }

    /**
     * Shift a value from the start of the list.
     *
     * @return ListIterator
     */
    public function shift()
    {
        array_shift($this->keys);

        return array_shift($this->values);
    }

    /**
     * Unshift a value onto the start of the list.
     *
     * @param mixed $value
     *
     * @return ListIterator
     */
    public function unshift($value)
    {
        $offset = $this->indexCounter;

        $this->indexCounter++;

        array_unshift($this->keys, $offset);
        array_unshift($this->values, $value);
    }

    /**
     * Return the list as an array
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return array_combine($this->keys, $this->values);
    }

    /**
     * Import keys and values from the given array
     *
     * @param array $arr
     *
     * @return ListIterator
     */
    private function fromArray(array $arr)
    {
        $this->keys         = array_keys($arr);
        $this->values       = array_values($arr);
        $this->indexCounter = 0;

        if (empty($arr)) {
            return $this;
        }

        $intKeys = array_filter(
            $this->keys,
            function ($value) {
                $intVal = (int) $value;

                if ("$intVal" === "$value") {
                    return true;
                }

                return false;
            }
        );

        $this->indexCounter = max($intKeys);

        return $this;
    }

    /**
     * Sort the list by value
     *
     * @return ListIterator
     */
    public function asort()
    {
        $arr = $this->getArrayCopy();

        asort($arr);

        $this->fromArray($arr);
    }

    /**
     * Sort the list by key
     *
     * @return ListIterator
     */
    public function ksort()
    {
        $arr = $this->getArrayCopy();

        ksort($arr);

        $this->fromArray($arr);
    }

    /**
     * Sort the list naturally by value, case insensitive
     *
     * @return ListIterator
     */
    public function natcasesort()
    {
        $arr = $this->getArrayCopy();

        natcasesort($arr);

        $this->fromArray($arr);
    }

    /**
     * Sort the list naturally by value
     *
     * @return ListIterator
     */
    public function natsort()
    {
        $arr = $this->getArrayCopy();

        natsort($arr);

        $this->fromArray($arr);
    }

    /**
     * Sort the list by value using user defined function.
     *
     * @param callable $cmpFunction
     *
     * @throws \RuntimeException
     * @return ListIterator
     */
    public function uasort($cmpFunction)
    {
        if (!is_callable($cmpFunction)) {
            throw new \RuntimeException('Unable to sort list using non-callable compare function.');
        }

        $arr = $this->getArrayCopy();

        uasort($arr, $cmpFunction);

        $this->fromArray($arr);
    }

    /**
     * Sort the list by key using user defined function.
     *
     * @param callable $cmpFunction
     *
     * @throws \RuntimeException
     * @return ListIterator
     */
    public function uksort($cmpFunction)
    {
        if (!is_callable($cmpFunction)) {
            throw new \RuntimeException('Unable to sort list using non-callable compare function.');
        }

        $arr = $this->getArrayCopy();

        uksort($arr, $cmpFunction);

        $this->fromArray($arr);
    }

    /**
     * Insert the given value into the list at the current position.
     * Current position is bumped up to compensate.
     *
     * @param mixed $value
     *
     * @return ListIterator
     */
    public function insert($value)
    {
        $offset = $this->indexCounter;

        $this->indexCounter++;

        array_splice($this->keys, $this->position, 0, array($offset));
        array_splice($this->values, $this->position, 0, array($value));

        $this->position++;

        return $this;
    }
}
