<?php

namespace Iterator\Depend;

use Depend\Abstraction\DescriptorInterface;
use Depend\Abstraction\SelfDescribableInterface;
use Iterator\ArrayPathIterator as BaseArrayPathIterator;

class ArrayPathIterator extends BaseArrayPathIterator implements SelfDescribableInterface
{
    /**
     * @inheritdoc
     */
    public static function describeSelf(DescriptorInterface $descriptor)
    {
        $descriptor->setIsShared(false);
    }
}
