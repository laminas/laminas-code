<?php

/**
 * @see       https://github.com/laminasframwork/laminas-code for the canonical source repository
 * @copyright https://github.com/laminasframwork/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminasframwork/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\TestAsset;

require_once __DIR__ . '/TraitWithSameMethods.php';
require_once __DIR__ . '/BarTrait.php';

class TestClassWithTraitAliases
{
    use BarTrait, FooTrait, TraitWithSameMethods {
        FooTrait::foo insteadof TraitWithSameMethods;
        TraitWithSameMethods::bar insteadof BarTrait;
        TraitWithSameMethods::bar insteadof FooTrait;
        TraitWithSameMethods::foo as private test;
    }

    public function bazFooBar()
    {
        
    }
}
