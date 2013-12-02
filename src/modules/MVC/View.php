<?php

namespace MVC;

abstract class View
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @return string
     */
    abstract public function toString();

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @param Model $model
     *
     * @return View
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }


}
