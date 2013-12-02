<?php

namespace Template;

use Template\Abstraction\AbstractTemplate;

class Page extends AbstractTemplate
{
    /**
     * @var Layout
     */
    protected $layout;

    /**
     * @var bool
     */
    protected $useLayout = false;

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @return string
     */
    public function toString()
    {
        if (!$this->useLayout || !($this->layout instanceof Layout)) {
            return parent::toString();
        }

        $this->layout->setContent(parent::toString());

        return $this->layout->toString();
    }

    /**
     * @param Layout $layout
     *
     * @return Page
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * @return Layout
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param boolean $useLayout
     *
     * @return Page
     */
    public function setUseLayout($useLayout)
    {
        $this->useLayout = $useLayout;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getUseLayout()
    {
        return $this->useLayout;
    }

    /**
     * @param array $data
     *
     * @return Page
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return Page
     */
    public function addData(array $data)
    {
        $this->data = array_replace_recursive($this->data, $data);

        return $this;
    }


}
