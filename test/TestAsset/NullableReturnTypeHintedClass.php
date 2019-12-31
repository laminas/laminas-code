<?php

namespace LaminasTest\Code\TestAsset;

class NullableReturnTypeHintedClass extends EmptyClass
{
    public function arrayReturn() : ?array
    {
    }

    public function callableReturn() : ?callable
    {
    }

    public function intReturn() : ?int
    {
    }

    public function floatReturn() : ?float
    {
    }

    public function stringReturn() : ?string
    {
    }

    public function boolReturn() : ?bool
    {
    }

    public function selfReturn() : ?self
    {
    }

    public function parentReturn() : ?parent
    {
    }

    public function classReturn() : ?NullableReturnTypeHintedClass
    {
    }

    public function otherClassReturn() : ?InternalHintsClass
    {
    }
}
