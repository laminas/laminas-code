<?php

namespace ZendTest\Code\TestAsset;

class NullableHintsClass
{
    public function arrayParameter(?array $foo)
    {
    }

    public function callableParameter(?callable $foo)
    {
    }

    public function intParameter(?int $foo)
    {
    }

    public function floatParameter(?float $foo)
    {
    }

    public function stringParameter(?string $foo)
    {
    }

    public function boolParameter(?bool $foo)
    {
    }

    public function selfParameter(?self $foo)
    {
    }

    public function nullDefaultHintsClassParameter(?NullDefaultHintsClass $foo)
    {
    }
}
