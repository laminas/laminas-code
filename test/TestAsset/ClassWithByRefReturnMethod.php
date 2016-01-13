<?php

namespace ZendTest\Code\TestAsset;

class ClassWithByRefReturnMethod
{
    public function & byRefReturn()
    {
        $foo = 'bar';

        return $foo;
    }
}
