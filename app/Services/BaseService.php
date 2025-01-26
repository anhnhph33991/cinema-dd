<?php

namespace App\Services;

abstract class BaseService
{
    protected $repository;

    public function __construct()
    {
        $this->setRepository();
    }

    abstract public function getRepository();

    public function setRepository()
    {
        $this->repository = app()->make(
            $this->getRepository()
        );
    }
}
