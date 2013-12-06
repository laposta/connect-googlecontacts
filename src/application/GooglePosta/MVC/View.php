<?php

namespace GooglePosta\MVC;

/**
 * Class View
 *
 * @package Totally200\MVC
 */
class View extends \MVC\View
{
    /**
     * @var mixed
     */
    protected $content;

    /**
     * @param mixed $value
     */
    public function setContent($value)
    {
        $this->content = $value;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return (string) $this->content;
    }
}
