<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

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
