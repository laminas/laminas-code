<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Generator;

use PHPUnit\Framework\TestCase;
use stdClass;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\TypeGenerator;
use Zend\Code\Generator\ValueGenerator;
use Zend\Code\Reflection\MethodReflection;
use ZendTest\Code\TestAsset\ClassWithByRefReturnMethod;
use ZendTest\Code\TestAsset\EmptyClass;
use ZendTest\Code\TestAsset\InternalHintsClass;
use ZendTest\Code\TestAsset\IterableHintsClass;
use ZendTest\Code\TestAsset\NullableReturnTypeHintedClass;
use ZendTest\Code\TestAsset\ObjectHintsClass;
use ZendTest\Code\TestAsset\ReturnTypeHintedClass;

use function array_filter;
use function array_shift;
use function strpos;

/**
 * @group Zend_Code_Generator
 * @group Zend_Code_Generator_Php
 */
class MethodGeneratorTest extends TestCase
{
    public function testMethodConstructor()
    {
        $methodGenerator = new MethodGenerator();
        self::assertInstanceOf(MethodGenerator::class, $methodGenerator);
    }

    public function testMethodParameterAccessors()
    {
        $methodGenerator = new MethodGenerator();
        $methodGenerator->setParameters(['one']);
        $params = $methodGenerator->getParameters();
        $param = array_shift($params);
        self::assertInstanceOf(ParameterGenerator::class, $param);
    }

    public function testMethodParameterMutator()
    {
        $methodGenerator = new MethodGenerator();

        $methodGenerator->setParameter('foo');
        $methodGenerator->setParameter(['name' => 'bar', 'type' => 'array']);
        $methodGenerator->setParameter(ParameterGenerator::fromArray(['name' => 'baz', 'type' => stdClass::class]));

        $params = $methodGenerator->getParameters();
        self::assertCount(3, $params);

        /** @var $foo ParameterGenerator */
        $foo = array_shift($params);
        self::assertInstanceOf(ParameterGenerator::class, $foo);
        self::assertEquals('foo', $foo->getName());

        $bar = array_shift($params);
        self::assertEquals(ParameterGenerator::fromArray(['name' => 'bar', 'type' => 'array']), $bar);

        /** @var $baz ParameterGenerator */
        $baz = array_shift($params);
        self::assertEquals('baz', $baz->getName());

        $this->expectException(InvalidArgumentException::class);
        $methodGenerator->setParameter(new stdClass());
    }

    public function testMethodBodyGetterAndSetter()
    {
        $method = new MethodGenerator();
        $method->setBody('Foo');
        self::assertEquals('Foo', $method->getBody());
    }

    public function testDocBlockGetterAndSetter()
    {
        $docblockGenerator = new DocBlockGenerator();

        $method = new MethodGenerator();
        $method->setDocBlock($docblockGenerator);
        self::assertSame($docblockGenerator, $method->getDocBlock());
    }

    public function testCopyMethodSignature()
    {
        $ref = new MethodReflection(TestAsset\TestSampleSingleClass::class, 'withParamsAndReturnType');

        $methodGenerator = MethodGenerator::copyMethodSignature($ref);
        $target = <<<'EOS'
    protected function withParamsAndReturnType($mixed, array $array, callable $callable, ?string $string = null, iterable $iterable = array(), ?int $int = 0) : bool
    {
    }

EOS;
        self::assertEquals($target, (string) $methodGenerator);
    }

    public function testMethodFromReflection()
    {
        $ref = new MethodReflection(TestAsset\TestSampleSingleClass::class, 'someMethod');

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
        self::assertEquals($target, (string) $methodGenerator);
    }

    public function testMethodFromReflectionMultiLinesIndention()
    {
        $ref = new MethodReflection(TestAsset\TestSampleSingleClassMultiLines::class, 'someMethod');

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
        self::assertEquals($target, (string) $methodGenerator);
    }

    /**
     * @group ZF-6444
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

        self::assertEquals($expected, $methodGenerator->generate());
    }

    /**
     * @group ZF-6444
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
        self::assertEquals($expected, $methodGenerator->generate());
    }

    /**
     * @group ZF-6444
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
        self::assertEquals($expected, $methodGenerator->generate());
    }

    /**
     * @group ZF-7205
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
        self::assertEquals($expected, $methodGeneratorProperty->generate());
    }

    /**
     * @group ZF-7268
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
        self::assertRegExp('/array \$options = array\(\)\)/', $generated, $generated);
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

        self::assertEquals('SampleMethod', $methodGenerator->getName());
        self::assertEquals('foo', $methodGenerator->getBody());
        self::assertInstanceOf(DocBlockGenerator::class, $methodGenerator->getDocBlock());
        self::assertTrue($methodGenerator->isAbstract());
        self::assertTrue($methodGenerator->isFinal());
        self::assertTrue($methodGenerator->isStatic());
        self::assertEquals(MethodGenerator::VISIBILITY_PROTECTED, $methodGenerator->getVisibility());
        self::assertInstanceOf(TypeGenerator::class, $methodGenerator->getReturnType());
        self::assertEquals('\\SampleType', $methodGenerator->getReturnType()->generate());
    }

    public function testCreateInterfaceMethodFromArray()
    {
        $methodGenerator = MethodGenerator::fromArray([
            'name'       => 'execute',
            'interface'  => true,
            'docblock'   => [
                'shortdescription' => 'Short Description',
            ],
        ]);

        $expected = <<<'CODE'
    /**
     * Short Description
     */
    public function execute(\Runnable $command);
CODE;

        $methodGenerator->setParameter(['name' => 'command', 'type' => 'Runnable']);

        self::assertTrue($methodGenerator->isInterface());
        self::assertEquals('execute', $methodGenerator->getName());
        self::assertEquals($expected, $methodGenerator->generate());
        self::assertInstanceOf(DocBlockGenerator::class, $methodGenerator->getDocBlock());
    }

    /**
     * @group zendframework/zend-code#29
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
     * @dataProvider returnTypeHintClasses
     *
     * @param string $className
     * @param string $methodName
     * @param string $expectedReturnSignature
     */
    public function testFrom($className, $methodName, $expectedReturnSignature)
    {
        $methodGenerator = MethodGenerator::fromReflection(new MethodReflection($className, $methodName));

        self::assertStringMatchesFormat('%A) : ' . $expectedReturnSignature . '%A{%A', $methodGenerator->generate());
    }

    public function returnTypeHintClasses()
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
            [ObjectHintsClass::class, 'objectReturnValue', 'object'],
            [ObjectHintsClass::class, 'nullableObjectReturnValue', '?object'],
        ];

        return array_filter(
            $parameters,
            function (array $parameter) {
                return PHP_VERSION_ID >= 70200
                    || (
                        false === strpos($parameter[2], 'object')
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
