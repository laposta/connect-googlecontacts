<?php

namespace GooglePosta\MVC;

use Template\Layout;
use Template\Page;

/**
 * Class View
 *
 * @package Totally200\MVC
 */
class View extends \MVC\View
{
    /**
     * @var Page
     */
    protected $page;

    /**
     * @var Layout
     */
    protected $layout;

    /**
     * @param Page     $page
     * @param Layout   $layout
     */
    function __construct(Page $page, Layout $layout)
    {
        $this->page   = $page;
        $this->layout = $layout;

        $this->page->setLayout($this->layout);
    }

    /**
     * @param string $filePath
     */
    public function setPage($filePath)
    {
        $this->page->setTemplate($filePath);
    }

    /**
     * @param string $filePath
     */
    public function setLayout($filePath)
    {
        $this->layout->setTemplate($filePath);

        $this->page->setUseLayout(true);
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->page->toString();
    }
}
