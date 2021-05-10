<?php

namespace LaminasTest\Code\TestAsset;

interface FooInterface extends \ArrayAccess
{
    public const BAR = 5;
    public const FOO = self::BAR;

    public function fooBarBaz();

}
