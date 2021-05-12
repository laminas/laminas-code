<?php

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
