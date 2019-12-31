<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\TestAsset;

use LaminasTest\Code\TestAsset\FooTrait;

class TestClassUsesTraitSimple
{
    use \LaminasTest\Code\TestAsset\BarTrait, FooTrait;
    use BazTrait;
}
