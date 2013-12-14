<?php

namespace DataStore;

use DataStore\Adapter\Abstraction\AdapterInterface;

class DataStore
{
    /**
     * @var mixed
     */
    private $content;

    /**
     * @param mixed $data
     *
     * @return DataStore
     */
    public function setContent($data)
    {
        $this->content = $data;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return bool
     */
    public function hasContent()
    {
        return !is_null($this->content);
    }

    /**
     * Persist the DataStore's content using the given adapter.
     *
     * @param AdapterInterface $adapter
     *
     * @return DataStore
     */
    public function persist(AdapterInterface $adapter)
    {
        $adapter->persist($this->getContent());

        return $this;
    }

    /**
     * Load DataStore content from the given adapter.
     *
     * @param AdapterInterface $adapter
     */
    public function retrieve(AdapterInterface $adapter)
    {
        $this->setContent($adapter->retrieve());
    }
} 
