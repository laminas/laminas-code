<?php

namespace LaminasTest\Code\Generator;

use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\TypeGenerator;
use Laminas\Code\Generator\ValueGenerator;
use Laminas\Code\Reflection\MethodReflection;
use LaminasTest\Code\TestAsset\ClassWithByRefReturnMethod;
use LaminasTest\Code\TestAsset\EmptyClass;
use LaminasTest\Code\TestAsset\InternalHintsClass;
use LaminasTest\Code\TestAsset\IterableHintsClass;
use LaminasTest\Code\TestAsset\NullableReturnTypeHintedClass;
use LaminasTest\Code\TestAsset\ObjectHintsClass;
use LaminasTest\Code\TestAsset\Php80Types;
use LaminasTest\Code\TestAsset\ReturnTypeHintedClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use stdClass;

use function array_filter;
use function array_map;
use function array_shift;
use function array_values;

#[Group('Laminas_Code_Generator')]
#[Group('Laminas_Code_Generator_Php')]
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
        $param  = array_shift($params);
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

        /** @var ParameterGenerator $foo */
        $foo = array_shift($params);
        self::assertInstanceOf(ParameterGenerator::class, $foo);
        self::assertSame('foo', $foo->getName());

        $bar = array_shift($params);
        self::assertEquals(ParameterGenerator::fromArray(['name' => 'bar', 'type' => 'array']), $bar);

        /** @var ParameterGenerator $baz */
        $baz = array_shift($params);
        self::assertSame('baz', $baz->getName());

        $this->expectException(InvalidArgumentException::class);
        $methodGenerator->setParameter(new stdClass());
    }

    public function testSetMethodParameter()
    {
        $methodGenerator = new MethodGenerator();

        $methodGenerator->setParameter('foo');

        $params = $methodGenerator->getParameters();
        self::assertCount(1, $params);

        /** @var ParameterGenerator $foo */
        $foo = array_shift($params);
        self::assertInstanceOf(ParameterGenerator::class, $foo);
        self::assertSame('foo', $foo->getName());
    }

    public function testSetMethodParameters()
    {
        $methodGenerator = new MethodGenerator();

        $methodGenerator->setParameter('foo');
        $methodGenerator->setParameter(['name' => 'bar', 'type' => 'array', 'position' => 2]);
        $methodGenerator->setParameter(
            ParameterGenerator::fromArray(['name' => 'baz', 'type' => stdClass::class, 'position' => 1])
        );

        $params = $methodGenerator->getParameters();

        $sorting = array_map(static fn(ParameterGenerator $parameter): string => $parameter->getName(), $params);

        self::assertEquals(['foo' => 'foo', 'baz' => 'baz', 'bar' => 'bar'], $sorting);
    }

    public function testMethodBodyGetterAndSetter()
    {
        $method = new MethodGenerator();
        $method->setBody('Foo');
        self::assertSame('Foo', $method->getBody());
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
        $target          = <<<'EOS'
    protected function withParamsAndReturnType($mixed, array $array, ?callable $callable = null, ?int $int = 0) : bool
    {
    }

EOS;
        self::assertSame($target, (string) $methodGenerator);
    }

    public function testCopyMethodSignatureForPromotedParameter(): void
    {
        $ref = new MethodReflection(TestAsset\ClassWithPromotedParameter::class, '__construct');

        $methodGenerator = MethodGenerator::copyMethodSignature($ref);
        $target          = <<<'EOS'
    public function __construct(private string $promotedParameter)
    {
    }

EOS;
        self::assertSame($target, (string) $methodGenerator);
    }

    public function testMethodFromReflection()
    {
        $ref = new MethodReflection(TestAsset\TestSampleSingleClass::class, 'someMethod');

        $methodGenerator = MethodGenerator::fromReflection($ref);
        $target          = <<<EOS
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
        self::assertSame($target, (string) $methodGenerator);
    }

    public function testMethodFromReflectionMultiLinesIndention()
    {
        $ref = new MethodReflection(TestAsset\TestSampleSingleClassMultiLines::class, 'someMethod');

        $methodGenerator = MethodGenerator::fromReflection($ref);
        $target          = <<<EOS
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
        self::assertSame($target, (string) $methodGenerator);
    }

    #[Group('Laminas-6444')]
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

        self::assertSame($expected, $methodGenerator->generate());
    }

    #[Group('Laminas-6444')]
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
        self::assertSame($expected, $methodGenerator->generate());
    }

    #[Group('Laminas-6444')]
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
        self::assertSame($expected, $methodGenerator->generate());
    }

    #[Group('Laminas-7205')]
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
        self::assertSame($expected, $methodGeneratorProperty->generate());
    }

    #[Group('Laminas-7268')]
    public function testDefaultValueGenerationDoesNotIncludeTrailingSemicolon()
    {
        $method  = new MethodGenerator('setOptions');
        $default = new ValueGenerator();
        $default->setValue([]);

        $param = new ParameterGenerator('options', 'array');
        $param->setDefaultValue($default);

        $method->setParameter($param);
        $generated = $method->generate();
        self::assertStringContainsString('array $options = [])', $generated);
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

        self::assertSame('SampleMethod', $methodGenerator->getName());
        self::assertSame('foo', $methodGenerator->getBody());
        self::assertInstanceOf(DocBlockGenerator::class, $methodGenerator->getDocBlock());
        self::assertTrue($methodGenerator->isAbstract());
        self::assertTrue($methodGenerator->isFinal());
        self::assertTrue($methodGenerator->isStatic());
        self::assertSame(MethodGenerator::VISIBILITY_PROTECTED, $methodGenerator->getVisibility());
        self::assertInstanceOf(TypeGenerator::class, $methodGenerator->getReturnType());
        self::assertSame('\\SampleType', $methodGenerator->getReturnType()->generate());
    }

    /**
     * @param bool $expected
     */
    #[DataProvider('returnsReferenceValues')]
    public function testCreateFromArrayWithReturnsReference(bool|string|int $value, $expected): void
    {
        $methodGenerator = MethodGenerator::fromArray([
            'name'             => 'SampleMethod',
            'returnsreference' => $value,
        ]);

        self::assertSame($expected, $methodGenerator->returnsReference());
    }

    /**
     * @return list<array{
     *     bool|string|int,
     *     bool
     * }>
     */
    public static function returnsReferenceValues(): array
    {
        return [
            [true, true],
            [1, true],
            ['true', true],
            [false, false],
            [0, false],
            ['', false],
        ];
    }

    public function testCreateInterfaceMethodFromArray()
    {
        $methodGenerator = MethodGenerator::fromArray([
            'name'      => 'execute',
            'interface' => true,
            'docblock'  => [
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
        self::assertSame('execute', $methodGenerator->getName());
        self::assertSame($expected, $methodGenerator->generate());
        self::assertInstanceOf(DocBlockGenerator::class, $methodGenerator->getDocBlock());
    }

    #[Group('zendframework/zend-code#29')]
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

    #[Group('zendframework/zend-code#29')]
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
     * @param string $className
     * @param string $methodName
     * @param string $expectedReturnSignature
     */
    #[DataProvider('returnTypeHintClasses')]
    #[Group('zendframework/zend-code#29')]
    public function testFrom($className, $methodName, $expectedReturnSignature)
    {
        $methodGenerator = MethodGenerator::fromReflection(new MethodReflection($className, $methodName));

        self::assertStringMatchesFormat('%A) : ' . $expectedReturnSignature . '%w{%A', $methodGenerator->generate());
    }

    /**
     * @return string[][]
     * @psalm-return list<array{class-string, non-empty-string, non-empty-string}>
     */
    public static function returnTypeHintClasses()
    {
        return [
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
            [Php80Types::class, 'mixedType', 'mixed'],
            [Php80Types::class, 'falseType', '\\' . Php80Types::class . '|false'],
            [Php80Types::class, 'unionNullableType', '?bool'],
            [Php80Types::class, 'unionReverseNullableType', '?bool'],
            [Php80Types::class, 'unionNullableTypeWithDefaultValue', 'bool|string|null'],
            [Php80Types::class, 'unionType', '\\' . Php80Types::class . '|\\' . stdClass::class],
            [Php80Types::class, 'staticType', 'static'],
        ];
    }

    #[Group('zendframework/zend-code#29')]
    public function testByRefReturnType()
    {
        $methodGenerator = new MethodGenerator('foo');

        $methodGenerator->setReturnsReference(true);

        self::assertStringMatchesFormat('%Apublic function & foo()%A', $methodGenerator->generate());

        $methodGenerator->setReturnsReference(false);

        self::assertStringMatchesFormat('%Apublic function foo()%A', $methodGenerator->generate());
    }

    #[Group('zendframework/zend-code#29')]
    public function testFromByReferenceMethodReflection()
    {
        $methodGenerator = MethodGenerator::fromReflection(
            new MethodReflection(ClassWithByRefReturnMethod::class, 'byRefReturn')
        );

        self::assertStringMatchesFormat('%Apublic function & byRefReturn()%A', $methodGenerator->generate());
    }

    /**
     * @psalm-param class-string $className
     * @psalm-param non-empty-string $method
     * @psalm-param non-empty-string $expectedGeneratedSignature
     */
    #[DataProvider('php80Methods')]
    #[Group('laminas/laminas-code#53')]
    public function testGeneratedReturnTypeForPhp80ReturnType(
        string $className,
        string $method,
        string $expectedType,
        string $expectedGeneratedSignature
    ): void {
        $generator  = MethodGenerator::fromReflection(new MethodReflection($className, $method));
        $returnType = $generator->getReturnType();

        self::assertNotNull($returnType);
        self::assertSame($expectedType, $returnType->__toString());
        self::assertSame($expectedGeneratedSignature, $returnType->generate());
    }

    /**
     * @psalm-return non-empty-list<array{class-string, non-empty-string, non-empty-string, non-empty-string}>
     */
    public static function php80Methods(): array
    {
        return [
            [Php80Types::class, 'mixedType', 'mixed', 'mixed'],
            [Php80Types::class, 'falseType', Php80Types::class . '|false', '\\' . Php80Types::class . '|false'],
            [Php80Types::class, 'unionNullableType', 'bool', '?bool'],
            [Php80Types::class, 'unionReverseNullableType', 'bool', '?bool'],
            [Php80Types::class, 'unionNullableTypeWithDefaultValue', 'bool|string|null', 'bool|string|null'],
            [
                Php80Types::class,
                'unionType',
                Php80Types::class . '|' . stdClass::class,
                '\\' . Php80Types::class . '|\\' . stdClass::class,
            ],
            [Php80Types::class, 'staticType', 'static', 'static'],
            [Php80Types::class, 'selfAndBoolType', Php80Types::class . '|bool', '\\' . Php80Types::class . '|bool'],
        ];
    }
}
