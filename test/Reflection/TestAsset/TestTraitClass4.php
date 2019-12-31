<?php
namespace LaminasTest\Code\Reflection\TestAsset;

use LaminasTest\Code\Reflection\TestAsset\TestTraitClass3 as TestTrait;

//issue #7428
class TestTraitClass4
{
    use TestTrait;
}
