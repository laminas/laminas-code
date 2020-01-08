<?php

namespace LaminasTest\Code\TestAsset;

class NullableHintsClass extends EmptyClass
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

    public function parentParameter(?parent $foo)
    {
    }

    public function nullableHintsClassParameter(?NullableHintsClass $foo)
    {
    }
}
