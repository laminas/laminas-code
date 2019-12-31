<?php

namespace LaminasTest\Code\TestAsset {

    use A\B\C as X;
use Foo\Bar;
use OtherThing\SomethingElse;
use Something\More as SM;

    class Baz
    {
        public function __construct(Bar\Boo $boo, Bam $bam)
        {

        }
    }

    class ExtendingSomethingMore extends SM\Blah
    {

    }

}


namespace {

    use X\Y\Z;

    class Foo
    {
        public function setGlobalStuff(GlobalStuff $stuff)
        {

        }
    }

}
