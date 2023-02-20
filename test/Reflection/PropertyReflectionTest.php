<?php

namespace LaminasTest\Code\Reflection;

use Laminas\Code\Reflection\ClassReflection;
use Laminas\Code\Reflection\PropertyReflection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Laminas_Reflection')]
#[Group('Laminas_Reflection_Property')]
class PropertyReflectionTest extends TestCase
{
    public function testDeclaringClassReturn()
    {
        $property = new PropertyReflection(TestAsset\TestSampleClass2::class, '_prop1');
        self::assertInstanceOf(ClassReflection::class, $property->getDeclaringClass());
        self::assertEquals(TestAsset\TestSampleClass2::class, $property->getDeclaringClass()->getName());
    }
}
