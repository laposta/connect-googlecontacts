<?php

namespace ApiHelper\Contacts\Entity\Collection;

use ApiHelper\Contacts\Abstraction\AbstractEntityCollection;
use ApiHelper\Contacts\Abstraction\FactoryInterface;
use ApiHelper\Contacts\Entity\Field;
use InvalidArgumentException;

/**
 * Class Contacts
 * @method Field current()
 * @method Field next()
 * @method Field offsetGet($index)
 *
 * @package ApiHelper\Contacts\Entity\Collection
 */
class Fields extends AbstractEntityCollection
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * Constructor override
     *
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        parent::__construct();

        $this->factory = $factory;
    }

    /**
     * Ensure the value is an Group and if not attempt to convert it.
     *
     * @param mixed $value
     *
     * @throws InvalidArgumentException
     * @return Field
     */
    protected function ratify($value)
    {
        if ($value instanceof Field) {
            return $value;
        }

        if (!isset($value['type']) || !isset($value['value'])) {
            throw new InvalidArgumentException("Unable to convert '$value' to a Field. Expected array('type'=>'','value'=>'').");
        }

        return $this->factory->createField($value['type'], $value['value']);
    }
}

