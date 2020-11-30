<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection;

use Laminas\Code\Reflection\ClassReflection;
use Laminas\Code\Reflection\PropertyReflection;
use Laminas\Code\Scanner\CachingFileScanner;
use LaminasTest\Code\Reflection\TestAsset\InjectablePropertyReflection;
use PHPUnit\Framework\TestCase;

use function get_class;

/**
 * @group      Laminas_Reflection
 * @group      Laminas_Reflection_Property
 */
class PropertyReflectionTest extends TestCase
{
    public function testDeclaringClassReturn()
    {
        $property = new PropertyReflection(TestAsset\TestSampleClass2::class, '_prop1');
        self::assertInstanceOf(ClassReflection::class, $property->getDeclaringClass());
        self::assertEquals(TestAsset\TestSampleClass2::class, $property->getDeclaringClass()->getName());
    }
}
