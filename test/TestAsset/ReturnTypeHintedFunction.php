<?php
/**
 * @author pifeifei <pifeifei1989@qq.com>
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

use LaminasTest\Code\TestAsset\InternalHintsClass;
use LaminasTest\Code\TestAsset\NullableReturnTypeHintedClass;
use LaminasTest\Code\TestAsset\ReturnTypeHintedClass;

function voidReturn() : void
{
}

function arrayReturn() : array
{
}

function callableReturn() : callable
{
}

function intReturn() : int
{
}

function floatReturn() : float
{
}

function stringReturn() : string
{
}

function boolReturn() : bool
{
}

function classReturn() : ReturnTypeHintedClass
{
}

function otherClassReturn() : InternalHintsClass
{
}

function nullableArrayReturn() : ?array
{
}

function nullableCallableReturn() : ?callable
{
}

function nullableIntReturn() : ?int
{
}

function nullableFloatReturn() : ?float
{
}

function nullableStringReturn() : ?string
{
}

function nullableBoolReturn() : ?bool
{
}

function nullableClassReturn() : ?NullableReturnTypeHintedClass
{
}

function nullableOtherClassReturn() : ?InternalHintsClass
{
}

function iterableReturnValue() : iterable
{
}

function nullableIterableReturnValue() : ?iterable
{
}


function objectReturnValue() : object
{
}

function nullableObjectReturnValue() : ?object
{
}