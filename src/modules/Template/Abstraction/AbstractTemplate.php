<?php

namespace Template\Abstraction;

use Printable;
use RuntimeException;

abstract class AbstractTemplate implements Printable
{
    /**
     * @var string
     */
    protected $template;

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $filePath
     *
     * @return $this
     */
    public function setTemplate($filePath)
    {
        $this->template = $filePath;

        return $this;
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    public function toString()
    {
        if (!is_readable($this->template)) {
            throw new RuntimeException("Unable to load file '$this->template'");
        }

        ob_start();

        require "$this->template";

        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
