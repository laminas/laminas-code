<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Reflection;

use Zend\Code\Reflection;
use ZendTest\Code\TestAsset\InternalHintsClass;

/**
 * @group      Zend_Reflection
 * @group      Zend_Reflection_Parameter
 */
class ParameterReflectionTest extends \PHPUnit_Framework_TestCase
{
    public function testDeclaringClassReturn()
    {
        $parameter = new Reflection\ParameterReflection(['ZendTest\Code\Reflection\TestAsset\TestSampleClass2', 'getProp2'], 0);
        $this->assertInstanceOf('Zend\Code\Reflection\ClassReflection', $parameter->getDeclaringClass());
    }

    public function testClassReturn_NoClassGiven_ReturnsNull()
    {
        $parameter = new Reflection\ParameterReflection(['ZendTest\Code\Reflection\TestAsset\TestSampleClass2', 'getProp2'], 'param1');
        $this->assertNull($parameter->getClass());
    }

    public function testClassReturn()
    {
        $parameter = new Reflection\ParameterReflection(['ZendTest\Code\Reflection\TestAsset\TestSampleClass2', 'getProp2'], 'param2');
        $this->assertInstanceOf('Zend\Code\Reflection\ClassReflection', $parameter->getClass());
    }

    /**
     * @dataProvider paramTypeTestProvider
     */
    public function testTypeReturn($param, $type)
    {
        $parameter = new Reflection\ParameterReflection(['ZendTest\Code\Reflection\TestAsset\TestSampleClass5', 'doSomething'], $param);
        $this->assertEquals($type, $parameter->detectType());
    }

    public function testCallableTypeHint()
    {
        $parameter = new Reflection\ParameterReflection(['ZendTest\Code\Reflection\TestAsset\CallableTypeHintClass', 'foo'], 'bar');
        $this->assertEquals('callable', $parameter->detectType());
    }

    public function paramTypeTestProvider()
    {
        return [
            ['one','int'],
            ['two','int'],
            ['three','string'],
            ['array','array'],
            ['class','ZendTest\Code\Reflection\TestAsset\TestSampleClass']
        ];
    }

    /**
     * @group zendframework/zend-code#29
     *
     * @dataProvider internalReflectionHintsProvider
     *
     * @param string $className
     * @param string $methodName
     * @param string $parameterName
     * @param string $expectedType
     */
    public function testGetType($className, $methodName, $parameterName, $expectedType)
    {
        $reflection = new Reflection\ParameterReflection(
            [$className, $methodName],
            $parameterName
        );

        $type = $reflection->getType();

        self::assertInstanceOf(\ReflectionType::class, $type);
        self::assertSame($expectedType, (string) $type);
    }

    /**
     * @group zendframework/zend-code#29
     *
     * @dataProvider internalReflectionHintsProvider
     *
     * @param string $className
     * @param string $methodName
     * @param string $parameterName
     * @param string $expectedType
     */
    public function testDetectType($className, $methodName, $parameterName, $expectedType)
    {
        $reflection = new Reflection\ParameterReflection(
            [$className, $methodName],
            $parameterName
        );

        self::assertSame($expectedType, $reflection->detectType());
    }

    /**
     * @return string[][]
     */
    public function internalReflectionHintsProvider()
    {
        return [
            [InternalHintsClass::class, 'arrayParameter', 'foo', 'array'],
            [InternalHintsClass::class, 'callableParameter', 'foo', 'callable'],
            [InternalHintsClass::class, 'intParameter', 'foo', 'int'],
            [InternalHintsClass::class, 'floatParameter', 'foo', 'float'],
            [InternalHintsClass::class, 'stringParameter', 'foo', 'string'],
            [InternalHintsClass::class, 'boolParameter', 'foo', 'bool'],
        ];
    }
}
