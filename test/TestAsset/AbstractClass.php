<?php

namespace LaminasTest\Code\TestAsset;

abstract class AbstractClass
{
    protected $config = [];

    public function getConfig()
    {
        return $this->config;
    }

    abstract public function helloWorld();
}
