<?php

namespace DataStore\Adapter\Abstraction;

interface AdapterInterface
{
    /**
     * Persist the given data
     *
     * @param mixed $data
     *
     * @return AdapterInterface
     */
    public function persist($data);

    /**
     * Retrieve and return previously persisted data.
     *
     * @return mixed
     */
    public function retrieve();
}
