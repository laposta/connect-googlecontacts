<?php

namespace Connect\Entity\Abstraction;

use Entity\Entity;
use Exception;

class ClearableEntity extends Entity
{
    public function clear()
    {
        for ($this->rewind(); $this->valid(); $this->next()) {
            $key = $this->key();
            $this->$key = null;
        }
    }
} 
