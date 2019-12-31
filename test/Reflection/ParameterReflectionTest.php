<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection;

use Laminas\Code\Reflection;
use LaminasTest\Code\TestAsset\ClassTypeHintedClass;
use LaminasTest\Code\TestAsset\DocBlockOnlyHintsClass;
use LaminasTest\Code\TestAsset\InternalHintsClass;

/**
 * @group      Laminas_Reflection
 * @group      Laminas_Reflection_Parameter
 */
class ParameterReflectionTest extends \PHPUnit_Framework_TestCase
{
    public function testDeclaringClassReturn()
    {
        $parameter = new Reflection\ParameterReflection(
            ['LaminasTest\Code\Reflection\TestAsset\TestSampleClass2', 'getProp2'],
            0
        );
        $this->assertInstanceOf('Laminas\Code\Reflection\ClassReflection', $parameter->getDeclaringClass());
    }

    public function testClassReturnNoClassGivenReturnsNull()
    {
        $parameter = new Reflection\ParameterReflection(
            ['LaminasTest\Code\Reflection\TestAsset\TestSampleClass2', 'getProp2'],
            'param1'
        );
        $this->assertNull($parameter->getClass());
    }

    public function testClassReturn()
    {
        $parameter = new Reflection\ParameterReflection(
            ['LaminasTest\Code\Reflection\TestAsset\TestSampleClass2', 'getProp2'],
            'param2'
        );
        $this->assertInstanceOf('Laminas\Code\Reflection\ClassReflection', $parameter->getClass());
    }

    /**
     * @dataProvider paramTypeTestProvider
     */
    public function testTypeReturn($param, $type)
    {
        $parameter = new Reflection\ParameterReflection(
            ['LaminasTest\Code\Reflection\TestAsset\TestSampleClass5', 'doSomething'],
            $param
        );
        $this->assertEquals($type, $parameter->detectType());
    }

    public function testCallableTypeHint()
    {
        $parameter = new Reflection\ParameterReflection(
            ['LaminasTest\Code\Reflection\TestAsset\CallableTypeHintClass', 'foo'],
            'bar'
        );
        $this->assertEquals('callable', $parameter->detectType());
    }

    public function paramTypeTestProvider()
    {
        return [
            ['one','int'],
            ['two','int'],
            ['three','string'],
            ['array','array'],
            ['class','LaminasTest\Code\Reflection\TestAsset\TestSampleClass']
        ];
    }

    /**
     * @group zendframework/zend-code#29
     *
     * @requires PHP 7.0
     *
     * @dataProvider reflectionHintsProvider
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
     * @requires PHP 7.0
     *
     * @dataProvider reflectionHintsProvider
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

        // following is just due to an incompatibility between this test method and `testGetType`
        if ('self' === $expectedType) {
            $expectedType = $className;
        }

        self::assertSame($expectedType, $reflection->detectType());
    }

    /**
     * @return string[][]
     */
    public function reflectionHintsProvider()
    {
        return [
            [InternalHintsClass::class, 'arrayParameter', 'foo', 'array'],
            [InternalHintsClass::class, 'callableParameter', 'foo', 'callable'],
            [InternalHintsClass::class, 'intParameter', 'foo', 'int'],
            [InternalHintsClass::class, 'floatParameter', 'foo', 'float'],
            [InternalHintsClass::class, 'stringParameter', 'foo', 'string'],
            [InternalHintsClass::class, 'boolParameter', 'foo', 'bool'],
            [ClassTypeHintedClass::class, 'selfParameter', 'foo', 'self'],
            [ClassTypeHintedClass::class, 'classParameter', 'foo', ClassTypeHintedClass::class],
            [ClassTypeHintedClass::class, 'otherClassParameter', 'foo', InternalHintsClass::class],
            [ClassTypeHintedClass::class, 'closureParameter', 'foo', \Closure::class],
            [ClassTypeHintedClass::class, 'importedClosureParameter', 'foo', \Closure::class],
        ];
    }

    /**
     * @group zendframework/zend-code#29
     *
     * @requires PHP 7.0
     *
     * @dataProvider docBlockHintsProvider
     *
     * @param string $className
     * @param string $methodName
     * @param string $parameterName
     */
    public function testGetTypeWithDocBlockOnlyTypes($className, $methodName, $parameterName)
    {
        $reflection = new Reflection\ParameterReflection(
            [$className, $methodName],
            $parameterName
        );

        self::assertNull($reflection->getType());
    }

    /**
     * @group zendframework/zend-code#29
     *
     * @dataProvider docBlockHintsProvider
     *
     * @param string $className
     * @param string $methodName
     * @param string $parameterName
     * @param string $expectedType
     */
    public function testDetectTypeWithDocBlockOnlyTypes($className, $methodName, $parameterName, $expectedType)
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
    public function docBlockHintsProvider()
    {
        return [
            [DocBlockOnlyHintsClass::class, 'arrayParameter', 'foo', 'array'],
            [DocBlockOnlyHintsClass::class, 'callableParameter', 'foo', 'callable'],
            [DocBlockOnlyHintsClass::class, 'intParameter', 'foo', 'int'],
            [DocBlockOnlyHintsClass::class, 'floatParameter', 'foo', 'float'],
            [DocBlockOnlyHintsClass::class, 'stringParameter', 'foo', 'string'],
            [DocBlockOnlyHintsClass::class, 'boolParameter', 'foo', 'bool'],
            [DocBlockOnlyHintsClass::class, 'selfParameter', 'foo', 'self'],
            [DocBlockOnlyHintsClass::class, 'classParameter', 'foo', 'DocBlockOnlyHintsClass'],
            [DocBlockOnlyHintsClass::class, 'otherClassParameter', 'foo', 'InternalHintsClass'],
        ];
    }
}
