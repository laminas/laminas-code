<?php

namespace LaminasTest\Code\Generator;

use Laminas\Code\Generator\AttributeGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use LaminasTest\Code\Generator\TestAsset\AttributeAsset;
use LaminasTest\Code\Generator\TestAsset\ClassWithAttributes;
use LaminasTest\Code\Generator\TestAsset\ClassWithInterface;
use LaminasTest\Code\Generator\TestAsset\TestEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use stdClass;

use function array_map;

#[CoversClass(AttributeGenerator::class)]
final class AttributeGeneratorTest extends TestCase
{
    /** @param array<array-key, mixed> $arguments */
    #[DataProvider('validAttributes')]
    public function testGeneratesExpectedCode(string $name, array $arguments, string $expected): void
    {
        self::assertSame($expected, AttributeGenerator::fromName($name, $arguments)->generate());
    }

    /** @return array<non-empty-string, array{string, array<array-key, mixed>, string}> */
    public static function validAttributes(): array
    {
        return [
            'no arguments'          => ['Foo', [], '#[\Foo]'],
            'leading backslash'     => ['\Foo\Bar', [], '#[\Foo\Bar]'],
            'positional arguments'  => ['Foo', [1, 'a'], "#[\Foo(1, 'a')]"],
            'named arguments'       => [
                'Foo',
                ['first' => true, 'second' => null],
                '#[\Foo(first: true, second: null)]',
            ],
            'mixed arguments'       => ['Foo', [0 => 1, 'named' => 2], '#[\Foo(1, named: 2)]'],
            'float keeps its type'  => ['Foo', [1.0], '#[\Foo(1.0)]'],
            'negative float'        => ['Foo', [-0.5], '#[\Foo(-0.5)]'],
            'escaped string'        => ['Foo', ["a'b\\c"], "#[\Foo('a\\'b\\\\c')]"],
            'list argument'         => ['Foo', [[1, 2]], '#[\Foo([1, 2])]'],
            'hash argument'         => ['Foo', [['a' => 1]], "#[\Foo(['a' => 1])]"],
            'nested array argument' => ['Foo', [[[1], ['b' => 2]]], "#[\Foo([[1], ['b' => 2]])]"],
            'sparse list argument'  => ['Foo', [[3 => 'a']], "#[\Foo([3 => 'a'])]"],
            'enum case argument'    => [
                'Foo',
                [TestEnum::Test1],
                '#[\Foo(\LaminasTest\Code\Generator\TestAsset\TestEnum::Test1)]',
            ],
        ];
    }

    public function testRejectsInvalidAttributeName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided attribute name "1nvalid" is not a valid class name');

        AttributeGenerator::fromName('1nvalid');
    }

    public function testRejectsEmptyAttributeName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        AttributeGenerator::fromName('');
    }

    public function testRejectsInvalidArgumentName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided argument name "not a name" is not a valid parameter name');

        AttributeGenerator::fromName('Foo', ['not a name' => 1]);
    }

    public function testRejectsPositionalArgumentsAfterNamedOnes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Positional arguments cannot follow named arguments in an attribute declaration'
        );

        AttributeGenerator::fromName('Foo', ['named' => 1, 2]);
    }

    public function testRejectsValuesThatAreNotConstantExpressions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Type "stdClass" cannot be used as an attribute argument');

        AttributeGenerator::fromName('Foo', [new stdClass()])->generate();
    }

    public function testRejectsValuesThatAreNotConstantExpressionsWhenNested(): void
    {
        $this->expectException(InvalidArgumentException::class);

        AttributeGenerator::fromName('Foo', [['nested' => new stdClass()]])->generate();
    }

    public function testExposesNameAndArguments(): void
    {
        $attribute = AttributeGenerator::fromName('\Foo\Bar', ['baz' => 1]);

        self::assertSame('Foo\Bar', $attribute->getName());
        self::assertSame(['baz' => 1], $attribute->getArguments());
    }

    public function testFromReflectionAttribute(): void
    {
        $attributes = (new ReflectionClass(ClassWithAttributes::class))->getAttributes(AttributeAsset::class);

        self::assertSame(
            '#[\LaminasTest\Code\Generator\TestAsset\AttributeAsset]',
            AttributeGenerator::fromReflectionAttribute($attributes[0])->generate()
        );
    }

    public function testFromReflectorOnClass(): void
    {
        self::assertSame(
            [
                '#[\LaminasTest\Code\Generator\TestAsset\AttributeAsset]',
                "#[\LaminasTest\Code\Generator\TestAsset\AttributeAsset(stringArgument: 'any string', intArgument: 1)]",
            ],
            self::generateAll(AttributeGenerator::fromReflector(new ReflectionClass(ClassWithAttributes::class)))
        );
    }

    public function testFromReflectorOnMethodPropertyAndParameter(): void
    {
        $method = new ReflectionMethod(ClassWithAttributes::class, 'method');

        self::assertSame(
            ["#[\LaminasTest\Code\Generator\TestAsset\AttributeAsset(stringArgument: 'on a method')]"],
            self::generateAll(AttributeGenerator::fromReflector($method))
        );

        self::assertSame(
            ["#[\LaminasTest\Code\Generator\TestAsset\AttributeAsset(stringArgument: 'on a property')]"],
            self::generateAll(AttributeGenerator::fromReflector(
                new ReflectionProperty(ClassWithAttributes::class, 'property')
            ))
        );

        self::assertSame(
            ['#[\LaminasTest\Code\Generator\TestAsset\AttributeAsset]'],
            self::generateAll(AttributeGenerator::fromReflector($method->getParameters()[0]))
        );
    }

    public function testFromReflectorOnUnannotatedSymbolProducesNoAttributes(): void
    {
        self::assertSame([], AttributeGenerator::fromReflector(new ReflectionClass(ClassWithInterface::class)));
    }

    /**
     * @param list<AttributeGenerator> $attributes
     * @return list<string>
     */
    private static function generateAll(array $attributes): array
    {
        return array_map(static fn (AttributeGenerator $attribute): string => $attribute->generate(), $attributes);
    }
}
