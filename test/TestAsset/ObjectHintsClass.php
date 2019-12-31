<?php

namespace LaminasTest\Code\TestAsset;

class ObjectHintsClass extends EmptyClass
{
    public function objectParameter(object $foo)
    {
    }

    public function nullableObjectParameter(?object $foo)
    {
    }

    public function nullDefaultObjectParameter(object $foo = null)
    {
    }

    public function objectReturnValue() : object
    {
    }

    public function nullableObjectReturnValue() : ?object
    {
    }
}
