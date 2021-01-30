<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator;

use Closure;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\ValueGenerator;
use Laminas\Code\Reflection\ClassReflection;
use Laminas\Code\Reflection\MethodReflection;
use Laminas\Code\Reflection\ParameterReflection;
use LaminasTest\Code\Generator\TestAsset\ParameterClass;
use LaminasTest\Code\TestAsset\ClassTypeHintedClass;
use LaminasTest\Code\TestAsset\DocBlockOnlyHintsClass;
use LaminasTest\Code\TestAsset\EmptyClass;
use LaminasTest\Code\TestAsset\InternalHintsClass;
use LaminasTest\Code\TestAsset\IterableHintsClass;
use LaminasTest\Code\TestAsset\NullableHintsClass;
use LaminasTest\Code\TestAsset\NullNullableDefaultHintsClass;
use LaminasTest\Code\TestAsset\ObjectHintsClass;
use LaminasTest\Code\TestAsset\Php80Types;
use LaminasTest\Code\TestAsset\VariadicParametersClass;
use Phar;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use stdClass;

use function array_combine;
use function array_filter;
use function array_map;
use function array_shift;
use function ltrim;
use function strpos;
use function strtolower;

use const PHP_VERSION_ID;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class ParameterGeneratorTest extends TestCase
{
    public function testTypeGetterAndSetterPersistValue()
    {
        $parameterGenerator = new ParameterGenerator();
        $parameterGenerator->setType('Foo');
        self::assertEquals('Foo', $parameterGenerator->getType());
    }

    public function testNameGetterAndSetterPersistValue()
    {
        $parameterGenerator = new ParameterGenerator();
        $parameterGenerator->setName('Foo');
        self::assertEquals('Foo', $parameterGenerator->getName());
    }

    public function testDefaultValueGetterAndSetterPersistValue()
    {
        $parameterGenerator = new ParameterGenerator();

        $value = new ValueGenerator('Foo', ValueGenerator::TYPE_CONSTANT);
        $parameterGenerator->setDefaultValue($value);
        self::assertEquals('Foo', (string) $parameterGenerator->getDefaultValue());
    }

    public function testPositionGetterAndSetterPersistValue()
    {
        $parameterGenerator = new ParameterGenerator();
        $parameterGenerator->setPosition(2);
        self::assertEquals(2, $parameterGenerator->getPosition());
    }

    public function testGenerateIsCorrect()
    {
        $parameterGenerator = new ParameterGenerator();
        $parameterGenerator->setType('Foo');
        $parameterGenerator->setName('bar');
        $parameterGenerator->setDefaultValue(15);
        self::assertEquals('\\Foo $bar = 15', $parameterGenerator->generate());

        $parameterGenerator->setDefaultValue('foo');
        self::assertEquals('\\Foo $bar = \'foo\'', $parameterGenerator->generate());
    }

    public function testFromReflectionGetParameterName()
    {
        $reflectionParameter = $this->getFirstReflectionParameter('name');
        $codeGenParam        = ParameterGenerator::fromReflection($reflectionParameter);

        self::assertEquals('param', $codeGenParam->getName());
    }

    public function testFromReflectionGetParameterType()
    {
        $reflectionParameter = $this->getFirstReflectionParameter('type');
        $codeGenParam        = ParameterGenerator::fromReflection($reflectionParameter);

        self::assertEquals('stdClass', $codeGenParam->getType());
    }

    public function testFromReflectionGetReference()
    {
        $reflectionParameter = $this->getFirstReflectionParameter('reference');
        $codeGenParam        = ParameterGenerator::fromReflection($reflectionParameter);

        self::assertTrue($codeGenParam->getPassedByReference());
    }

    public function testFromReflectionGetDefaultValue()
    {
        $reflectionParameter = $this->getFirstReflectionParameter('defaultValue');
        $codeGenParam        = ParameterGenerator::fromReflection($reflectionParameter);

        $defaultValue = $codeGenParam->getDefaultValue();
        self::assertEquals('\'foo\'', (string) $defaultValue);
    }

    /**
     * @group 95
     */
    public function testFromReflectionGetDefaultValueNotOptional()
    {
        $method = new MethodReflection(ParameterClass::class, 'defaultObjectEqualsNullAndNotOptional');

        $params = $method->getParameters();

        self::assertCount(2, $params);

        $firstParameter = ParameterGenerator::fromReflection($params[0]);
        self::assertInstanceOf(ValueGenerator::class, $firstParameter->getDefaultValue());
        self::assertNull($firstParameter->getDefaultValue()->getSourceContent());
    }

    public function testFromReflectionGetArrayHint()
    {
        $reflectionParameter = $this->getFirstReflectionParameter('fromArray');
        $codeGenParam        = ParameterGenerator::fromReflection($reflectionParameter);

        self::assertEquals('array', $codeGenParam->getType());
    }

    public function testFromReflectionGetWithNativeType()
    {
        $reflectionParameter = $this->getFirstReflectionParameter('hasNativeDocTypes');
        $codeGenParam        = ParameterGenerator::fromReflection($reflectionParameter);

        self::assertNotEquals('int', $codeGenParam->getType());
        self::assertEquals('', $codeGenParam->getType());
    }

    public function testCallableTypeHint()
    {
        $parameter = ParameterGenerator::fromReflection(
            new ParameterReflection([TestAsset\CallableTypeHintClass::class, 'foo'], 'bar')
        );

        self::assertEquals('callable', $parameter->getType());
    }

    /**
     * @dataProvider dataFromReflectionGenerate
     * @param string $methodName
     * @param string $expectedCode
     */
    public function testFromReflectionGenerate($methodName, $expectedCode)
    {
        $reflectionParameter = $this->getFirstReflectionParameter($methodName);
        $codeGenParam        = ParameterGenerator::fromReflection($reflectionParameter);

        self::assertEquals($expectedCode, $codeGenParam->generate());
    }

    /**
     * @return string[][]
     * @psalm-return non-empty-list<array{non-empty-string, non-empty-string}>
     */
    public function dataFromReflectionGenerate(): array
    {
        return [
            ['name', '$param'],
            ['type', '\\stdClass $bar'],
            ['reference', '&$baz'],
            ['defaultValue', '$value = \'foo\''],
            ['defaultNull', '$value = null'],
            ['fromArray', 'array $array'],
            ['hasNativeDocTypes', '$integer'],
            ['defaultArray', '$array = []'],
            ['defaultArrayWithValues', '$array = [1, 2, 3]'],
            ['defaultFalse', '$val = false'],
            ['defaultTrue', '$val = true'],
            ['defaultZero', '$number = 0'],
            ['defaultNumber', '$number = 1234'],
            ['defaultFloat', '$float = 1.34'],
            ['defaultConstant', '$con = \'foo\''],
        ];
    }

    /**
     * @param string $method
     * @return ParameterReflection
     */
    protected function getFirstReflectionParameter($method)
    {
        $reflectionClass = new ClassReflection(ParameterClass::class);
        $method          = $reflectionClass->getMethod($method);

        $params = $method->getParameters();

        return array_shift($params);
    }

    public function testCreateFromArray()
    {
        $parameterGenerator = ParameterGenerator::fromArray([
            'name'              => 'SampleParameter',
            'type'              => 'int',
            'defaultvalue'      => 'default-foo',
            'passedbyreference' => false,
            'position'          => 1,
            'sourcedirty'       => false,
            'sourcecontent'     => 'foo',
            'indentation'       => '-',
            'omitdefaultvalue'  => true,
        ]);

        self::assertEquals('SampleParameter', $parameterGenerator->getName());
        self::assertEquals('int', $parameterGenerator->getType());
        self::assertInstanceOf(ValueGenerator::class, $parameterGenerator->getDefaultValue());
        self::assertFalse($parameterGenerator->getPassedByReference());
        self::assertEquals(1, $parameterGenerator->getPosition());
        self::assertFalse($parameterGenerator->isSourceDirty());
        self::assertEquals('foo', $parameterGenerator->getSourceContent());
        self::assertEquals('-', $parameterGenerator->getIndentation());
        self::assertStringNotContainsString('default-foo', $parameterGenerator->generate());

        $reflectionOmitDefaultValue = new ReflectionProperty($parameterGenerator, 'omitDefaultValue');

        $reflectionOmitDefaultValue->setAccessible(true);

        self::assertTrue($reflectionOmitDefaultValue->getValue($parameterGenerator));
    }

    /**
     * @group 4988
     */
    public function testParameterGeneratorReturnsCorrectTypeForNonNamespaceClasses()
    {
        require_once __DIR__ . '/../TestAsset/NonNamespaceClass.php';

        $reflClass = new ClassReflection('LaminasTest_Code_NsTest_BarClass');
        $params    = $reflClass->getMethod('fooMethod')->getParameters();

        $param = ParameterGenerator::fromReflection($params[0]);

        self::assertEquals('LaminasTest_Code_NsTest_BarClass', $param->getType());
    }

    /**
     * @group 5193
     */
    public function testTypehintsWithNamespaceInNamepsacedClassReturnTypewithBackslash()
    {
        require_once __DIR__ . '/TestAsset/NamespaceTypeHintClass.php';

        $reflClass = new ClassReflection('Namespaced\TypeHint\Bar');
        $params    = $reflClass->getMethod('method')->getParameters();

        $param = ParameterGenerator::fromReflection($params[0]);

        self::assertEquals('OtherNamespace\ParameterClass', $param->getType());
    }

    /**
     * @group 6023
     * @coversNothing
     */
    public function testGeneratedParametersHaveEscapedDefaultValues()
    {
        $parameter = new ParameterGenerator();

        $parameter->setName('foo');
        $parameter->setDefaultValue("\\'");
        $parameter->setType('stdClass');

        self::assertSame("\\stdClass \$foo = '\\\\\\''", $parameter->generate());
    }

    /**
     * @group zendframework/zend-code#29
     * @dataProvider simpleHints
     * @param string $type
     * @param string $expectedType
     */
    public function testGeneratesSimpleHints($type, $expectedType)
    {
        $parameter = new ParameterGenerator();

        $parameter->setName('foo');
        $parameter->setType($type);

        self::assertSame($expectedType . ' $foo', $parameter->generate());
    }

    /**
     * @return string[][]
     */
    public function simpleHints()
    {
        return [
            ['callable', 'callable'],
            ['Callable', 'callable'],
            ['CALLABLE', 'callable'],
            ['array', 'array'],
            ['Array', 'array'],
            ['ARRAY', 'array'],
            ['string', 'string'],
            ['String', 'string'],
            ['STRING', 'string'],
            ['bool', 'bool'],
            ['Bool', 'bool'],
            ['BOOL', 'bool'],
            ['int', 'int'],
            ['Int', 'int'],
            ['INT', 'int'],
            ['float', 'float'],
            ['Float', 'float'],
            ['FLOAT', 'float'],
        ];
    }

    /**
     * @group zendframework/zend-code#29
     * @dataProvider validClassName
     * @param string $className
     */
    public function testTypeHintWithValidClassName($className)
    {
        $parameter = new ParameterGenerator();

        $parameter->setName('foo');
        $parameter->setType($className);

        self::assertSame('\\' . $className . ' $foo', $parameter->generate());
    }

    /**
     * @return string[][]
     */
    public function validClassName()
    {
        return [
            ['stdClass'],
            ['foo'],
            ['FOO'],
            ['bar'],
            ['bar1'],
            ['BAR1'],
            ['baz\\tab'],
            ['baz\\tab\\taz'],
            ['baz\\tab\\taz1'],
            ['resource'],
            ['Resource'],
            ['RESOURCE'],
        ];
    }

    /**
     * @group zendframework/zend-code#29
     * @dataProvider reflectionHints
     * @param string      $className
     * @param string      $methodName
     * @param string      $parameterName
     * @param string|null $expectedType
     */
    public function testTypeHintFromReflection($className, $methodName, $parameterName, $expectedType)
    {
        $parameter = ParameterGenerator::fromReflection(new ParameterReflection(
            [$className, $methodName],
            $parameterName
        ));

        if (null === $expectedType) {
            self::assertNull($parameter->getType());

            return;
        }

        self::assertSame(ltrim($expectedType, '?\\'), $parameter->getType());
    }

    /**
     * @group zendframework/zend-code#29
     * @dataProvider reflectionHints
     * @param string      $className
     * @param string      $methodName
     * @param string      $parameterName
     * @param string|null $expectedType
     */
    public function testTypeHintFromReflectionGeneratedCode($className, $methodName, $parameterName, $expectedType)
    {
        $parameter = ParameterGenerator::fromReflection(new ParameterReflection(
            [$className, $methodName],
            $parameterName
        ));

        if (null === $expectedType) {
            self::assertStringStartsWith('$' . $parameterName, $parameter->generate());

            return;
        }

        self::assertStringStartsWith($expectedType . ' $' . $parameterName, $parameter->generate());
    }

    /**
     * @return string[][]
     */
    public function reflectionHints()
    {
        $parameters = [
            [InternalHintsClass::class, 'arrayParameter', 'foo', 'array'],
            [InternalHintsClass::class, 'callableParameter', 'foo', 'callable'],
            [InternalHintsClass::class, 'intParameter', 'foo', 'int'],
            [InternalHintsClass::class, 'floatParameter', 'foo', 'float'],
            [InternalHintsClass::class, 'stringParameter', 'foo', 'string'],
            [InternalHintsClass::class, 'boolParameter', 'foo', 'bool'],
            [NullableHintsClass::class, 'arrayParameter', 'foo', '?array'],
            [NullableHintsClass::class, 'callableParameter', 'foo', '?callable'],
            [NullableHintsClass::class, 'intParameter', 'foo', '?int'],
            [NullableHintsClass::class, 'floatParameter', 'foo', '?float'],
            [NullableHintsClass::class, 'stringParameter', 'foo', '?string'],
            [NullableHintsClass::class, 'boolParameter', 'foo', '?bool'],
            [NullableHintsClass::class, 'selfParameter', 'foo', '?\\' . NullableHintsClass::class],
            [NullableHintsClass::class, 'parentParameter', 'foo', '?\\' . EmptyClass::class],
            [NullableHintsClass::class, 'nullableHintsClassParameter', 'foo', '?\\' . NullableHintsClass::class],
            [NullNullableDefaultHintsClass::class, 'arrayParameter', 'foo', '?array'],
            [NullNullableDefaultHintsClass::class, 'callableParameter', 'foo', '?callable'],
            [NullNullableDefaultHintsClass::class, 'intParameter', 'foo', '?int'],
            [NullNullableDefaultHintsClass::class, 'floatParameter', 'foo', '?float'],
            [NullNullableDefaultHintsClass::class, 'stringParameter', 'foo', '?string'],
            [NullNullableDefaultHintsClass::class, 'boolParameter', 'foo', '?bool'],
            [
                NullNullableDefaultHintsClass::class,
                'selfParameter',
                'foo',
                '?\\' . NullNullableDefaultHintsClass::class,
            ],
            [NullNullableDefaultHintsClass::class, 'parentParameter', 'foo', '?\\' . EmptyClass::class],
            [
                NullNullableDefaultHintsClass::class,
                'nullableDefaultHintsClassParameter',
                'foo',
                '?\\' . NullNullableDefaultHintsClass::class,
            ],
            [ClassTypeHintedClass::class, 'selfParameter', 'foo', '\\' . ClassTypeHintedClass::class],
            [ClassTypeHintedClass::class, 'parentParameter', 'foo', '\\' . EmptyClass::class],
            [ClassTypeHintedClass::class, 'classParameter', 'foo', '\\' . ClassTypeHintedClass::class],
            [ClassTypeHintedClass::class, 'otherClassParameter', 'foo', '\\' . InternalHintsClass::class],
            [ClassTypeHintedClass::class, 'closureParameter', 'foo', '\\' . Closure::class],
            [ClassTypeHintedClass::class, 'importedClosureParameter', 'foo', '\\' . Closure::class],
            [DocBlockOnlyHintsClass::class, 'arrayParameter', 'foo', null],
            [DocBlockOnlyHintsClass::class, 'callableParameter', 'foo', null],
            [DocBlockOnlyHintsClass::class, 'intParameter', 'foo', null],
            [DocBlockOnlyHintsClass::class, 'floatParameter', 'foo', null],
            [DocBlockOnlyHintsClass::class, 'stringParameter', 'foo', null],
            [DocBlockOnlyHintsClass::class, 'boolParameter', 'foo', null],
            [DocBlockOnlyHintsClass::class, 'selfParameter', 'foo', null],
            [DocBlockOnlyHintsClass::class, 'classParameter', 'foo', null],
            [DocBlockOnlyHintsClass::class, 'otherClassParameter', 'foo', null],
            [IterableHintsClass::class, 'iterableParameter', 'foo', 'iterable'],
            [IterableHintsClass::class, 'nullableIterableParameter', 'foo', '?iterable'],
            [IterableHintsClass::class, 'nullDefaultIterableParameter', 'foo', '?iterable'],
            [ObjectHintsClass::class, 'objectParameter', 'foo', 'object'],
            [ObjectHintsClass::class, 'nullableObjectParameter', 'foo', '?object'],
            [ObjectHintsClass::class, 'nullDefaultObjectParameter', 'foo', '?object'],
        ];

        $compatibleParameters = array_filter(
            $parameters,
            function (array $parameter) {
                return PHP_VERSION_ID >= 70200
                    || (
                        false === strpos($parameter[3], 'object')
                    );
            }
        );

        // just re-organizing the keys so that the phpunit data set makes sense in errors:
        return array_combine(
            array_map(
                function (array $definition) {
                    return $definition[0] . '#' . $definition[1];
                },
                $compatibleParameters
            ),
            $compatibleParameters
        );
    }

    /**
     * @group zendframework/zend-code#29
     * @dataProvider variadicHints
     * @param string $className
     * @param string $methodName
     * @param string $parameterName
     * @param string $expectedGeneratedSignature
     */
    public function testVariadicArgumentFromReflection(
        $className,
        $methodName,
        $parameterName,
        $expectedGeneratedSignature
    ) {
        $parameter = ParameterGenerator::fromReflection(new ParameterReflection(
            [$className, $methodName],
            $parameterName
        ));

        self::assertTrue($parameter->getVariadic());
        self::assertNull($parameter->getDefaultValue());
        self::assertSame($expectedGeneratedSignature, $parameter->generate());
    }

    /**
     * @return string[][]
     */
    public function variadicHints()
    {
        return [
            [VariadicParametersClass::class, 'firstVariadicParameter', 'foo', '... $foo'],
            [VariadicParametersClass::class, 'secondVariadicParameter', 'bar', '... $bar'],
            [
                VariadicParametersClass::class,
                'typeHintedVariadicParameter',
                'bar',
                '\\' . VariadicParametersClass::class . ' ... $bar',
            ],
            [
                VariadicParametersClass::class,
                'byRefVariadic',
                'bar',
                '&... $bar',
            ],
            [
                VariadicParametersClass::class,
                'byRefTypeHintedVariadic',
                'bar',
                '\\' . VariadicParametersClass::class . ' &... $bar',
            ],
        ];
    }

    /**
     * @group zendframework/zend-code#29
     */
    public function testSetGetVariadic()
    {
        $parameter = new ParameterGenerator('foo');

        self::assertFalse($parameter->getVariadic(), 'Is not variadic by default');
        self::assertSame('$foo', $parameter->generate());

        $parameter->setVariadic(true);

        self::assertTrue($parameter->getVariadic());
        self::assertSame('... $foo', $parameter->generate());

        $parameter->setVariadic(false);

        self::assertFalse($parameter->getVariadic());
        self::assertSame('$foo', $parameter->generate());
    }

    /**
     * @group zendframework/zend-code#29
     */
    public function testGetInternalClassDefaultParameterValue()
    {
        $parameter = ParameterGenerator::fromReflection(new ParameterReflection([Phar::class, 'compress'], 1));

        self::assertSame('null', strtolower((string) $parameter->getDefaultValue()));
    }

    /**
     * @requires PHP >= 8.0
     * @group laminas/laminas-code#53
     * @dataProvider php80Methods
     * @psalm-param class-string $className
     * @psalm-param non-empty-string $method
     * @psalm-param positive-int|0 $parameterIndex
     * @psalm-param non-empty-string $expectedGeneratedSignature
     */
    public function testGeneratedSignatureForPhp80ParameterType(
        string $className,
        string $method,
        int $parameterIndex,
        string $expectedType,
        string $expectedGeneratedSignature
    ): void {
        $parameter = ParameterGenerator::fromReflection(
            new ParameterReflection([$className, $method], $parameterIndex)
        );

        self::assertSame($expectedType, $parameter->getType());
        self::assertSame($expectedGeneratedSignature, $parameter->generate());
    }

    /**
     * @psalm-return non-empty-list<array{class-string, non-empty-string, positive-int|0, string, non-empty-string}>
     */
    public function php80Methods(): array
    {
        return [
            [Php80Types::class, 'mixedType', 0, 'mixed', 'mixed $parameter'],
            [
                Php80Types::class,
                'falseType',
                0,
                Php80Types::class . '|false',
                '\\' . Php80Types::class . '|false $parameter',
            ],
            [Php80Types::class, 'unionNullableType', 0, 'bool', '?bool $parameter'],
            [Php80Types::class, 'unionReverseNullableType', 0, 'bool', '?bool $parameter'],
            [
                Php80Types::class,
                'unionNullableTypeWithDefaultValue',
                0,
                'bool|string|null',
                'bool|string|null $parameter = null',
            ],
            [
                Php80Types::class,
                'unionType',
                0,
                Php80Types::class . '|' . stdClass::class,
                '\\' . Php80Types::class . '|\\' . stdClass::class . ' $parameter',
            ],
            [
                Php80Types::class,
                'selfAndBoolType',
                0,
                Php80Types::class . '|bool',
                '\\' . Php80Types::class . '|bool $parameter',
            ],
        ];
    }

    public function testOmitType()
    {
        $parameter = new ParameterGenerator('foo', 'string', 'bar');
        $parameter->omitDefaultValue();

        self::assertEquals('string $foo', $parameter->generate());
    }
}
