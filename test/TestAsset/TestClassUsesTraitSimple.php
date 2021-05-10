<?php

namespace LaminasTest\Code\TestAsset;

use LaminasTest\Code\TestAsset\FooTrait;

class TestClassUsesTraitSimple
{
    use \LaminasTest\Code\TestAsset\BarTrait, FooTrait;
    use BazTrait;
}
