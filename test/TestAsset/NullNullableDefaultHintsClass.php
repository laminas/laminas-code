<?php

namespace LaminasTest\Code\TestAsset;

class NullNullableDefaultHintsClass extends EmptyClass
{
    public function arrayParameter(?array $foo = null)
    {
    }

    public function callableParameter(?callable $foo = null)
    {
    }

    public function intParameter(?int $foo = null)
    {
    }

    public function floatParameter(?float $foo = null)
    {
    }

    public function stringParameter(?string $foo = null)
    {
    }

    public function boolParameter(?bool $foo = null)
    {
    }

    public function selfParameter(?self $foo = null)
    {
    }

    public function parentParameter(?parent $foo = null)
    {
    }

    public function nullableDefaultHintsClassParameter(?NullNullableDefaultHintsClass $foo = null)
    {
    }
}
