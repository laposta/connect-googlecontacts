<?php

namespace Iterator;

/**
 * Class LinkedKeyIterator
 * A list containing flipped key => value pairs simulating key => key pairs.
 * Using either key or value as the index returns its corresponding counterpart.
 * This class is ideally suited for bidirectional mapping. The only requirements
 * is that all values are also valid keys (i.e. string or integer).
 * This class allows only one-to-one relationships. Use LinkedMultiKeyIterator for
 * a many-to-one implementation.
 *
 * @package Iterator
 */
class LinkedKeyIterator extends ArrayIterator
{
    /**
     * @var ArrayIterator
     */
    protected $secondary;

    /**
     * @var bool
     */
    protected $isDirty = true;

    /**
     * Initialize the linked key iterator
     */
    protected function resetSecondary()
    {
        $this->secondary = new ArrayIterator(array_flip($this->getArrayCopy()), $this->getFlags());
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($index)
    {
        if ($this->isDirty) {
            $this->resetSecondary();

            $this->isDirty = false;
        }

        if (parent::offsetExists($index)) {
            return true;
        }

        return $this->secondary->offsetExists($index);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($index)
    {
        if ($this->isDirty) {
            $this->resetSecondary();

            $this->isDirty = false;
        }

        if (parent::offsetExists($index)) {
            return parent::offsetGet($index);
        }

        return $this->secondary->offsetGet($index);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($index, $newval)
    {
        if (!is_scalar($newval)) {
            $val = serialize($newval);
            throw new \InvalidArgumentException("Unable to accept '$val' for '$index'. Value must be a scalar");
        }

        parent::offsetSet($index, $newval);

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
    public function setFlags($flags)
    {
        parent::setFlags($flags);

        $this->secondary->setFlags($flags);
    }

    /**
     * @inheritdoc
     */
    public function asort()
    {
        parent::asort();

        $this->isDirty = true;
    }

    /**
     * @inheritdoc
     */
    public function ksort()
    {
        parent::ksort();

        $this->isDirty = true;
    }

    /**
     * @inheritdoc
     */
    public function uasort($cmp_function)
    {
        parent::uasort($cmp_function);

        $this->isDirty = true;
    }

    /**
     * @inheritdoc
     */
    public function uksort($cmp_function)
    {
        parent::uksort($cmp_function);

        $this->isDirty = true;
    }

    /**
     * @inheritdoc
     */
    public function natsort()
    {
        parent::natsort();

        $this->isDirty = true;
    }

    /**
     * @inheritdoc
     */
    public function natcasesort()
    {
        parent::natcasesort();

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
        unset($this->secondary);

        return parent::serialize();
    }
}
