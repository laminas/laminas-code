<?php

namespace LaminasTest\Code\TestAsset;

use Closure;

class ClassTypeHintedClass extends EmptyClass
{
    public function selfParameter(self $foo)
    {
    }

    public function parentParameter(parent $foo)
    {
    }

    public function classParameter(ClassTypeHintedClass $foo)
    {
    }

    public function otherClassParameter(InternalHintsClass $foo)
    {
    }

    public function closureParameter(\Closure $foo)
    {
    }

    public function importedClosureParameter(Closure $foo)
    {
    }
}
