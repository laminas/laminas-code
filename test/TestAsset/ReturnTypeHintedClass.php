<?php

namespace LaminasTest\Code\TestAsset;

class ReturnTypeHintedClass extends EmptyClass
{
    public function voidReturn() : void
    {
    }

    public function arrayReturn() : array
    {
    }

    public function callableReturn() : callable
    {
    }

    public function intReturn() : int
    {
    }

    public function floatReturn() : float
    {
    }

    public function stringReturn() : string
    {
    }

    public function boolReturn() : bool
    {
    }

    public function selfReturn() : self
    {
    }

    public function parentReturn() : parent
    {
    }

    public function classReturn() : ReturnTypeHintedClass
    {
    }

    public function otherClassReturn() : InternalHintsClass
    {
    }
}
