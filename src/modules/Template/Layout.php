<?php

namespace Template;

use Template\Abstraction\AbstractTemplate;
use Template\Abstraction\ElementFactoryInterface;
use Template\Abstraction\NodeInterface;
use Template\Node\Collection\NodeList;

class Layout extends AbstractTemplate
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var NodeList
     */
    protected $styles;

    /**
     * @var NodeList
     */
    protected $scripts;

    /**
     * @var ElementFactoryInterface
     */
    protected $elementFactory;

    /**
     * @var boolean
     */
    protected $renderingLayout = false;

    /**
     * @param NodeList                $nodeListPrototype
     * @param ElementFactoryInterface $elementFactory
     */
    public function __construct(NodeList $nodeListPrototype, ElementFactoryInterface $elementFactory)
    {
        $this->styles         = clone $nodeListPrototype;
        $this->scripts        = clone $nodeListPrototype;
        $this->elementFactory = $elementFactory;
    }

    /**
     * @param string $content
     *
     * @return Layout
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $title
     *
     * @return Layout
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $path
     *
     * @return Layout
     */
    public function addScript($path)
    {
        $element = $this->elementFactory->get('Script');
        $element->addAttribute('type', 'text/javascript');
        $element->addAttribute('src', $path);

        return $this->attachScript($element);
    }

    /**
     * @param string $path
     * @param string $media
     *
     * @return Layout
     */
    public function addStyle($path, $media = '')
    {
        $element = $this->elementFactory->get('Link');
        $element->addAttribute('rel', 'stylesheet');
        $element->addAttribute('type', 'text/css');
        $element->addAttribute('href', $path);

        if (!empty($media)) {
            $element->addAttribute('media', $media);
        }

        return $this->attachStyle($element);
    }

    /**
     * @param string $css
     *
     * @return Layout
     */
    public function addCss($css)
    {
        $element = $this->elementFactory->get('Style');
        $element->addAttribute('type', 'text/css');
        $element->addContent($css);

        return $this->attachStyle($element);
    }

    /**
     * @param string $js
     *
     * @return Layout
     */
    public function addJs($js)
    {
        $element = $this->elementFactory->get('Script');
        $element->addAttribute('type', 'text/javascript');
        $element->addContent($js);

        return $this->attachScript($element);
    }

    /**
     * @return NodeList
     */
    public function getStyles()
    {
        return $this->styles;
    }

    /**
     * @return NodeList
     */
    public function getScripts()
    {
        return $this->scripts;
    }

    /**
     * @return string
     */
    public function toString()
    {
        $this->renderingLayout = true;

        $this->scripts->rewind();
        $this->styles->rewind();

        return parent::toString();
    }

    /**
     * @param NodeInterface $element
     *
     * @return Layout
     */
    protected function attachScript(NodeInterface $element)
    {
        $this->attachElement($this->scripts, $element);

        return $this;
    }

    /**
     * @param NodeInterface $element
     *
     * @return Layout
     */
    protected function attachStyle(NodeInterface $element)
    {
        $this->attachElement($this->styles, $element);

        return $this;
    }

    /**
     * Attach an element to its collection.
     *
     * @param NodeList      $collection
     * @param NodeInterface $element
     *
     * @return Layout
     */
    protected function attachElement(NodeList $collection, NodeInterface $element)
    {
        if ($this->renderingLayout) {
            $collection->insert($element);
        }
        else {
            $collection->push($element);
        }

        return $this;
    }
}
