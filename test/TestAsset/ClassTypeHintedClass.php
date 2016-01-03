<?php

namespace ZendTest\Code\TestAsset;

class ClassTypeHintedClass
{
    public function selfParameter(self $foo)
    {
    }

    public function classParameter(ClassTypeHintedClass $foo)
    {
    }

    public function otherClassParameter(InternalHintsClass $foo)
    {
    }
}
