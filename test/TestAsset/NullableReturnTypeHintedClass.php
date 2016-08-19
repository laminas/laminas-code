<?php

namespace ZendTest\Code\TestAsset;

class NullableReturnTypeHintedClass
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

    public function classReturn() : ?ReturnTypeHintedClass
    {
    }

    public function otherClassReturn() : ?InternalHintsClass
    {
    }
}
