<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator;

use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\ValueGenerator;
use Laminas\Code\Reflection\MethodReflection;
use LaminasTest\Code\TestAsset\ClassWithByRefReturnMethod;
use LaminasTest\Code\TestAsset\EmptyClass;
use LaminasTest\Code\TestAsset\InternalHintsClass;
use LaminasTest\Code\TestAsset\IterableHintsClass;
use LaminasTest\Code\TestAsset\NullableReturnTypeHintedClass;
use LaminasTest\Code\TestAsset\ReturnTypeHintedClass;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class MethodGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testMethodConstructor()
    {
        $methodGenerator = new MethodGenerator();
        $this->isInstanceOf($methodGenerator, '\Laminas\Code\Generator\PhpMethod');
    }

    public function testMethodParameterAccessors()
    {
        $methodGenerator = new MethodGenerator();
        $methodGenerator->setParameters(['one']);
        $params = $methodGenerator->getParameters();
        $param = array_shift($params);
        $this->assertInstanceOf('Laminas\Code\Generator\ParameterGenerator', $param);
    }

    public function testMethodParameterMutator()
    {
        $methodGenerator = new MethodGenerator();

        $methodGenerator->setParameter('foo');
        $methodGenerator->setParameter(['name' => 'bar', 'type' => 'array']);
        $methodGenerator->setParameter(ParameterGenerator::fromArray(['name' => 'baz', 'type' => '\stdClass']));

        $params = $methodGenerator->getParameters();
        $this->assertCount(3, $params);

        /** @var $foo ParameterGenerator */
        $foo = array_shift($params);
        $this->assertInstanceOf('Laminas\Code\Generator\ParameterGenerator', $foo);
        $this->assertEquals('foo', $foo->getName());

        $bar = array_shift($params);
        $this->assertEquals(ParameterGenerator::fromArray(['name' => 'bar', 'type' => 'array']), $bar);

        /** @var $baz ParameterGenerator */
        $baz = array_shift($params);
        $this->assertEquals('baz', $baz->getName());

        $this->setExpectedException('Laminas\Code\Generator\Exception\InvalidArgumentException');
        $methodGenerator->setParameter(new \stdClass());
    }

    public function testMethodBodyGetterAndSetter()
    {
        $method = new MethodGenerator();
        $method->setBody('Foo');
        $this->assertEquals('Foo', $method->getBody());
    }

    public function testDocBlockGetterAndSetter()
    {
        $docblockGenerator = new \Laminas\Code\Generator\DocBlockGenerator();

        $method = new MethodGenerator();
        $method->setDocBlock($docblockGenerator);
        $this->assertSame($docblockGenerator, $method->getDocBlock());
    }


    public function testMethodFromReflection()
    {
        $ref = new MethodReflection('LaminasTest\Code\Generator\TestAsset\TestSampleSingleClass', 'someMethod');

        $methodGenerator = MethodGenerator::fromReflection($ref);
        $target = <<<EOS
    /**
     * Enter description here...
     *
     * @return bool
     */
    public function someMethod()
    {
        /* test test */
    }

EOS;
        $this->assertEquals($target, (string) $methodGenerator);
    }


    public function testMethodFromReflectionMultiLinesIndention()
    {
        $ref = new MethodReflection('LaminasTest\Code\Generator\TestAsset\TestSampleSingleClassMultiLines', 'someMethod');

        $methodGenerator = MethodGenerator::fromReflection($ref);
        $target = <<<EOS
    /**
     * Enter description here...
     *
     * @return bool
     */
    public function someMethod()
    {
        /* test test */

        /* test test */

        /* test test */
    }

EOS;
        $this->assertEquals($target, (string) $methodGenerator);
    }

    /**
     * @group Laminas-6444
     */
    public function testMethodWithStaticModifierIsEmitted()
    {
        $methodGenerator = new MethodGenerator();
        $methodGenerator->setName('foo');
        $methodGenerator->setParameters(['one']);
        $methodGenerator->setStatic(true);

        $expected = <<<EOS
    public static function foo(\$one)
    {
    }

EOS;

        $this->assertEquals($expected, $methodGenerator->generate());
    }

    /**
     * @group Laminas-6444
     */
    public function testMethodWithFinalModifierIsEmitted()
    {
        $methodGenerator = new MethodGenerator();
        $methodGenerator->setName('foo');
        $methodGenerator->setParameters(['one']);
        $methodGenerator->setFinal(true);

        $expected = <<<EOS
    final public function foo(\$one)
    {
    }

EOS;
        $this->assertEquals($expected, $methodGenerator->generate());
    }

    /**
     * @group Laminas-6444
     */
    public function testMethodWithFinalModifierIsNotEmittedWhenMethodIsAbstract()
    {
        $methodGenerator = new MethodGenerator();
        $methodGenerator->setName('foo');
        $methodGenerator->setParameters(['one']);
        $methodGenerator->setFinal(true);
        $methodGenerator->setAbstract(true);

        $expected = <<<EOS
    abstract public function foo(\$one);
EOS;
        $this->assertEquals($expected, $methodGenerator->generate());
    }

    /**
     * @group Laminas-7205
     */
    public function testMethodCanHaveDocBlock()
    {
        $methodGeneratorProperty = new MethodGenerator(
            'someFoo',
            [],
            MethodGenerator::FLAG_STATIC | MethodGenerator::FLAG_PROTECTED,
            null,
            '@var string $someVal This is some val'
        );

        $expected = <<<EOS
    /**
     * @var string \$someVal This is some val
     */
    protected static function someFoo()
    {
    }

EOS;
        $this->assertEquals($expected, $methodGeneratorProperty->generate());
    }

    /**
     * @group Laminas-7268
     */
    public function testDefaultValueGenerationDoesNotIncludeTrailingSemicolon()
    {
        $method = new MethodGenerator('setOptions');
        $default = new ValueGenerator();
        $default->setValue([]);

        $param   = new ParameterGenerator('options', 'array');
        $param->setDefaultValue($default);

        $method->setParameter($param);
        $generated = $method->generate();
        $this->assertRegexp('/array \$options = array\(\)\)/', $generated, $generated);
    }

    public function testCreateFromArray()
    {
        $methodGenerator = MethodGenerator::fromArray([
            'name'       => 'SampleMethod',
            'body'       => 'foo',
            'docblock'   => [
                'shortdescription' => 'foo',
            ],
            'abstract'   => true,
            'final'      => true,
            'static'     => true,
            'visibility' => MethodGenerator::VISIBILITY_PROTECTED,
            'returntype' => '\\SampleType',
        ]);

        $this->assertEquals('SampleMethod', $methodGenerator->getName());
        $this->assertEquals('foo', $methodGenerator->getBody());
        $this->assertInstanceOf('Laminas\Code\Generator\DocBlockGenerator', $methodGenerator->getDocBlock());
        $this->assertTrue($methodGenerator->isAbstract());
        $this->assertTrue($methodGenerator->isFinal());
        $this->assertTrue($methodGenerator->isStatic());
        $this->assertEquals(MethodGenerator::VISIBILITY_PROTECTED, $methodGenerator->getVisibility());
        $this->assertInstanceOf('Laminas\Code\Generator\TypeGenerator', $methodGenerator->getReturnType());
        $this->assertEquals('\\SampleType', $methodGenerator->getReturnType()->generate());
    }

    public function testCreateInterfaceMethodFromArray()
    {
        $methodGenerator = MethodGenerator::fromArray([
            'name'       => 'execute',
            'interface'  => true,
            'docblock'   => [
                'shortdescription' => 'Short Description',
            ]
        ]);

        $expected = <<<'CODE'
    /**
     * Short Description
     */
    public function execute(\Runnable $command);
CODE;

        $methodGenerator->setParameter(['name' => 'command', 'type' => 'Runnable']);

        $this->assertTrue($methodGenerator->isInterface());
        $this->assertEquals('execute', $methodGenerator->getName());
        $this->assertEquals($expected, $methodGenerator->generate());
        $this->assertInstanceOf('Laminas\Code\Generator\DocBlockGenerator', $methodGenerator->getDocBlock());
    }

    /**
     * @group zendframework/zend-code#29
     *
     * @requires PHP 7.0
     */
    public function testSetReturnType()
    {
        $methodGenerator = new MethodGenerator();

        $methodGenerator->setName('foo');
        $methodGenerator->setReturnType('bar');

        $expected = <<<'PHP'
    public function foo() : \bar
    {
    }

PHP;
        self::assertSame($expected, $methodGenerator->generate());
    }

    /**
     * @group zendframework/zend-code#29
     *
     * @requires PHP 7.0
     */
    public function testSetReturnTypeWithNull()
    {
        $methodGenerator = new MethodGenerator();

        $methodGenerator->setName('foo');
        $methodGenerator->setReturnType(null);

        $expected = <<<'PHP'
    public function foo()
    {
    }

PHP;
        self::assertSame($expected, $methodGenerator->generate());
    }

    /**
     * @group zendframework/zend-code#29
     *
     * @requires PHP 7.0
     *
     * @dataProvider returnTypeHintClassesProvider
     *
     * @param string $className
     * @param string $methodName
     * @param string $expectedReturnSignature
     */
    public function testFrom(string $className, string $methodName, string $expectedReturnSignature)
    {
        $methodGenerator = MethodGenerator::fromReflection(new MethodReflection($className, $methodName));

        self::assertStringMatchesFormat('%A) : ' . $expectedReturnSignature . '%A{%A', $methodGenerator->generate());
    }

    public function returnTypeHintClassesProvider()
    {
        $parameters = [
            [ReturnTypeHintedClass::class, 'voidReturn', 'void'],
            [ReturnTypeHintedClass::class, 'arrayReturn', 'array'],
            [ReturnTypeHintedClass::class, 'callableReturn', 'callable'],
            [ReturnTypeHintedClass::class, 'intReturn', 'int'],
            [ReturnTypeHintedClass::class, 'floatReturn', 'float'],
            [ReturnTypeHintedClass::class, 'stringReturn', 'string'],
            [ReturnTypeHintedClass::class, 'boolReturn', 'bool'],
            [ReturnTypeHintedClass::class, 'selfReturn', '\\' . ReturnTypeHintedClass::class],
            [ReturnTypeHintedClass::class, 'parentReturn', '\\' . EmptyClass::class],
            [ReturnTypeHintedClass::class, 'classReturn', '\\' . ReturnTypeHintedClass::class],
            [ReturnTypeHintedClass::class, 'otherClassReturn', '\\' . InternalHintsClass::class],
            [NullableReturnTypeHintedClass::class, 'arrayReturn', '?array'],
            [NullableReturnTypeHintedClass::class, 'callableReturn', '?callable'],
            [NullableReturnTypeHintedClass::class, 'intReturn', '?int'],
            [NullableReturnTypeHintedClass::class, 'floatReturn', '?float'],
            [NullableReturnTypeHintedClass::class, 'stringReturn', '?string'],
            [NullableReturnTypeHintedClass::class, 'boolReturn', '?bool'],
            [NullableReturnTypeHintedClass::class, 'selfReturn', '?\\' . NullableReturnTypeHintedClass::class],
            [NullableReturnTypeHintedClass::class, 'parentReturn', '?\\' . EmptyClass::class],
            [NullableReturnTypeHintedClass::class, 'classReturn', '?\\' . NullableReturnTypeHintedClass::class],
            [NullableReturnTypeHintedClass::class, 'otherClassReturn', '?\\' . InternalHintsClass::class],
            [IterableHintsClass::class, 'iterableReturnValue', 'iterable'],
            [IterableHintsClass::class, 'nullableIterableReturnValue', '?iterable'],
        ];

        return array_filter(
            $parameters,
            function (array $parameter) {
                return PHP_VERSION_ID >= 70100
                    || (
                        false === strpos($parameter[2], '?')
                        && ! in_array(strtolower($parameter[2]), ['void', 'iterable'])
                    );
            }
        );
    }

    /**
     * @group zendframework/zend-code#29
     */
    public function testByRefReturnType()
    {
        $methodGenerator = new MethodGenerator('foo');

        $methodGenerator->setReturnsReference(true);

        self::assertStringMatchesFormat('%Apublic function & foo()%A', $methodGenerator->generate());

        $methodGenerator->setReturnsReference(false);

        self::assertStringMatchesFormat('%Apublic function foo()%A', $methodGenerator->generate());
    }

    /**
     * @group zendframework/zend-code#29
     */
    public function testFromByReferenceMethodReflection()
    {
        $methodGenerator = MethodGenerator::fromReflection(
            new MethodReflection(ClassWithByRefReturnMethod::class, 'byRefReturn')
        );

        self::assertStringMatchesFormat('%Apublic function & byRefReturn()%A', $methodGenerator->generate());
    }
}
