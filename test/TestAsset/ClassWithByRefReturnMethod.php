<?php

namespace LaminasTest\Code\TestAsset;

class ClassWithByRefReturnMethod
{
    public function & byRefReturn()
    {
        $foo = 'bar';

        return $foo;
    }
}
