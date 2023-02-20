<?php

namespace LaminasTest\Code\Generator;

use Generator;
use Laminas\Code\Generator\DocBlock\Tag\VarTag;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\Exception\RuntimeException;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\PropertyValueGenerator;
use Laminas\Code\Generator\TypeGenerator;
use Laminas\Code\Generator\ValueGenerator;
use Laminas\Code\Reflection\ClassReflection;
use Laminas\Code\Reflection\PropertyReflection;
use LaminasTest\Code\Generator\TestAsset\ClassWithTypedProperty;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use stdClass;

use function addslashes;
use function array_shift;
use function str_replace;
use function uniqid;

#[Group('Laminas_Code_Generator')]
#[Group('Laminas_Code_Generator_Php')]
class PropertyGeneratorTest extends TestCase
{
    public function testPropertyConstructor(): void
    {
        $codeGenProperty = new PropertyGenerator();
        self::assertInstanceOf(PropertyGenerator::class, $codeGenProperty);
    }

    /**
     * @return bool[][]|string[][]|int[][]|null[][]
     */
    public static function dataSetTypeSetValueGenerate(): array
    {
        return [
            ['string', 'foo', "'foo';"],
            ['int', 1, '1;'],
            ['integer', 1, '1;'],
            ['bool', true, 'true;'],
            ['bool', false, 'false;'],
            ['boolean', true, 'true;'],
            ['number', 1, '1;'],
            ['float', 1.23, '1.23;'],
            ['double', 1.23, '1.23;'],
            ['constant', 'FOO', 'FOO;'],
            ['null', null, 'null;'],
        ];
    }

    #[DataProvider('dataSetTypeSetValueGenerate')]
    public function testSetTypeSetValueGenerate(string $type, mixed $value, string $code): void
    {
        $defaultValue = new PropertyValueGenerator();
        $defaultValue->setType($type);
        $defaultValue->setValue($value);

        self::assertSame($type, $defaultValue->getType());
        self::assertSame($code, $defaultValue->generate());
    }

    #[DataProvider('dataSetTypeSetValueGenerate')]
    public function testSetBogusTypeSetValueGenerateUseAutoDetection(string $type, mixed $value, string $code): void
    {
        if ('constant' === $type) {
            self::markTestSkipped('constant can only be detected explicitly');
        }

        $defaultValue = new PropertyValueGenerator();
        $defaultValue->setType('bogus');
        $defaultValue->setValue($value);

        self::assertSame($code, $defaultValue->generate());
    }

    public function testPropertyReturnsSimpleValue(): void
    {
        $codeGenProperty = new PropertyGenerator('someVal', 'some string value');
        self::assertSame('    public $someVal = \'some string value\';', $codeGenProperty->generate());
    }

    public function testPropertyMultilineValue(): void
    {
        $targetValue = [
            5,
            'one'   => 1,
            'two'   => '2',
            'null'  => null,
            'true'  => true,
            "bar's" => "bar's",
        ];

        $expectedSource = <<<EOS
    public \$myFoo = [
        5,
        'one' => 1,
        'two' => '2',
        'null' => null,
        'true' => true,
        'bar\'s' => 'bar\'s',
    ];
EOS;

        $property = new PropertyGenerator('myFoo', $targetValue);

        $targetSource = $property->generate();
        $targetSource = str_replace("\r", '', $targetSource);

        self::assertSame($expectedSource, $targetSource);
    }

    public static function visibility(): Generator
    {
        yield 'public' => [PropertyGenerator::FLAG_PUBLIC, 'public'];
        yield 'protected' => [PropertyGenerator::FLAG_PROTECTED, 'protected'];
        yield 'private' => [PropertyGenerator::FLAG_PRIVATE, 'private'];
    }

    #[DataProvider('visibility')]
    public function testPropertyCanProduceConstantWithVisibility(int $flag, string $visibility): void
    {
        $codeGenProperty = new PropertyGenerator('FOO', 'bar', [PropertyGenerator::FLAG_CONSTANT, $flag]);
        self::assertSame('    ' . $visibility . ' const FOO = \'bar\';', $codeGenProperty->generate());
    }

    public function testPropertyCanProduceConstantModifier(): void
    {
        $codeGenProperty = new PropertyGenerator('someVal', 'some string value', PropertyGenerator::FLAG_CONSTANT);
        self::assertSame('    public const someVal = \'some string value\';', $codeGenProperty->generate());
    }

    public function testPropertyCanProduceFinalConstantModifier(): void
    {
        $codeGenProperty = new PropertyGenerator(
            'someVal',
            'some string value',
            PropertyGenerator::FLAG_CONSTANT | PropertyGenerator::FLAG_FINAL
        );
        self::assertSame('    final public const someVal = \'some string value\';', $codeGenProperty->generate());
    }

    #[DataProvider('visibility')]
    public function testPropertyCanProduceReadonlyModifier(int $flag, string $visibility): void
    {
        $codeGenProperty = new PropertyGenerator(
            'someVal',
            'some string value',
            PropertyGenerator::FLAG_READONLY | $flag
        );

        self::assertSame(
            '    ' . $visibility . ' readonly $someVal = \'some string value\';',
            $codeGenProperty->generate()
        );
    }

    public function testFailToProduceReadonlyStatic(): void
    {
        $codeGenProperty = new PropertyGenerator('someVal', 'some string value');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Modifier "readonly" in combination with "static" not permitted.');

        $codeGenProperty->setFlags(PropertyGenerator::FLAG_READONLY | PropertyGenerator::FLAG_STATIC);
    }

    public function testFailToProduceReadonlyConstant(): void
    {
        $codeGenProperty = new PropertyGenerator('someVal', 'some string value');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Modifier "readonly" in combination with "constant" not permitted.');

        $codeGenProperty->setFlags(PropertyGenerator::FLAG_READONLY | PropertyGenerator::FLAG_CONSTANT);
    }

    #[Group('PR-704')]
    public function testPropertyCanProduceConstantModifierWithSetter(): void
    {
        $codeGenProperty = new PropertyGenerator('someVal', 'some string value');
        $codeGenProperty->setConst(true);
        self::assertSame('    public const someVal = \'some string value\';', $codeGenProperty->generate());
    }

    public function testPropertyCanProduceStaticModifier(): void
    {
        $codeGenProperty = new PropertyGenerator('someVal', 'some string value', PropertyGenerator::FLAG_STATIC);
        self::assertSame('    public static $someVal = \'some string value\';', $codeGenProperty->generate());
    }

    #[Group('Laminas-6444')]
    public function testPropertyWillLoadFromReflection(): void
    {
        $reflectionClass = new ClassReflection(TestAsset\TestClassWithManyProperties::class);

        // test property 1
        $reflProp = $reflectionClass->getProperty('_bazProperty');

        $cgProp = PropertyGenerator::fromReflection($reflProp);

        self::assertSame('_bazProperty', $cgProp->getName());
        $bazPropertyValue = $cgProp->getDefaultValue();
        self::assertNotNull($bazPropertyValue);
        self::assertSame([true, false, true], $bazPropertyValue->getValue());
        self::assertSame('private', $cgProp->getVisibility());

        $reflProp = $reflectionClass->getProperty('_bazStaticProperty');

        // test property 2
        $cgProp = PropertyGenerator::fromReflection($reflProp);

        self::assertSame('_bazStaticProperty', $cgProp->getName());
        $bazStaticValue = $cgProp->getDefaultValue();
        self::assertNotNull($bazStaticValue);
        self::assertSame(TestAsset\TestClassWithManyProperties::FOO, $bazStaticValue->getValue());
        self::assertTrue($cgProp->isStatic());
        self::assertSame('private', $cgProp->getVisibility());
    }

    #[Group('Laminas-6444')]
    public function testPropertyWillEmitStaticModifier(): void
    {
        $codeGenProperty = new PropertyGenerator(
            'someVal',
            'some string value',
            PropertyGenerator::FLAG_STATIC | PropertyGenerator::FLAG_PROTECTED
        );
        self::assertSame('    protected static $someVal = \'some string value\';', $codeGenProperty->generate());
    }

    #[Group('Laminas-7205')]
    public function testPropertyCanHaveDocBlock(): void
    {
        $codeGenProperty = new PropertyGenerator(
            'someVal',
            'some string value',
            PropertyGenerator::FLAG_STATIC | PropertyGenerator::FLAG_PROTECTED
        );

        $codeGenProperty->setDocBlock('@var string $someVal This is some val');

        $expected = <<<EOS
    /**
     * @var string \$someVal This is some val
     */
    protected static \$someVal = 'some string value';
EOS;
        self::assertSame($expected, $codeGenProperty->generate());
    }

    public function testOtherTypesThrowExceptionOnGenerate(): void
    {
        $codeGenProperty = new PropertyGenerator('someVal', new stdClass());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Type "stdClass" is unknown or cannot be used as property default value');

        $codeGenProperty->generate();
    }

    public function testCreateFromArray(): void
    {
        $propertyGenerator = PropertyGenerator::fromArray([
            'name'             => 'SampleProperty',
            'const'            => false,
            'defaultvalue'     => 'default-foo',
            'docblock'         => [
                'shortdescription' => 'foo',
            ],
            'abstract'         => true,
            'final'            => true,
            'static'           => true,
            'visibility'       => PropertyGenerator::VISIBILITY_PROTECTED,
            'omitdefaultvalue' => true,
            'type'             => TypeGenerator::fromTypeString(self::class),
        ]);

        self::assertSame('SampleProperty', $propertyGenerator->getName());
        self::assertFalse($propertyGenerator->isConst());
        self::assertFalse($propertyGenerator->isReadonly());
        self::assertInstanceOf(ValueGenerator::class, $propertyGenerator->getDefaultValue());
        self::assertInstanceOf(DocBlockGenerator::class, $propertyGenerator->getDocBlock());
        self::assertTrue($propertyGenerator->isAbstract());
        self::assertTrue($propertyGenerator->isFinal());
        self::assertTrue($propertyGenerator->isStatic());
        self::assertSame(PropertyGenerator::VISIBILITY_PROTECTED, $propertyGenerator->getVisibility());
        self::assertStringNotContainsString('default-foo', $propertyGenerator->generate());
        self::assertEquals(self::class, $propertyGenerator->getType());
        self::assertInstanceOf(TypeGenerator::class, $propertyGenerator->getType());
        $reflectionOmitDefaultValue = new ReflectionProperty($propertyGenerator, 'omitDefaultValue');

        $reflectionOmitDefaultValue->setAccessible(true);

        self::assertTrue($reflectionOmitDefaultValue->getValue($propertyGenerator));
    }

    public function testCreateReadonlyFromArray(): void
    {
        $propertyGenerator = PropertyGenerator::fromArray([
            'name'     => 'ReadonlyProperty',
            'readonly' => true,
        ]);

        self::assertSame('ReadonlyProperty', $propertyGenerator->getName());
        self::assertFalse($propertyGenerator->isConst());
        self::assertFalse($propertyGenerator->isAbstract());
        self::assertFalse($propertyGenerator->isFinal());
        self::assertFalse($propertyGenerator->isStatic());
        self::assertTrue($propertyGenerator->isReadonly());
        self::assertSame(PropertyGenerator::VISIBILITY_PUBLIC, $propertyGenerator->getVisibility());
    }

    #[Group('3491')]
    public function testPropertyDocBlockWillLoadFromReflection(): void
    {
        $reflectionClass = new ClassReflection(TestAsset\TestClassWithManyProperties::class);

        $reflProp = $reflectionClass->getProperty('fooProperty');
        $cgProp   = PropertyGenerator::fromReflection($reflProp);

        self::assertSame('fooProperty', $cgProp->getName());

        $docBlock = $cgProp->getDocBlock();
        self::assertInstanceOf(DocBlockGenerator::class, $docBlock);
        $tags = $docBlock->getTags();
        self::assertIsArray($tags);
        self::assertCount(1, $tags);
        $tag = array_shift($tags);
        self::assertInstanceOf(VarTag::class, $tag);
        self::assertSame('var', $tag->getName());
    }

    #[DataProvider('dataSetTypeSetValueGenerate')]
    public function testSetDefaultValue(string $type, mixed $value): void
    {
        $property = new PropertyGenerator();
        $property->setDefaultValue($value, $type);

        $valueGenerator = $property->getDefaultValue();
        self::assertNotNull($valueGenerator);
        self::assertSame($type, $valueGenerator->getType());
        self::assertSame($value, $valueGenerator->getValue());
    }

    public function testOmitType()
    {
        $property = new PropertyGenerator('foo', null);
        $property->omitDefaultValue();

        self::assertSame('    public $foo;', $property->generate());
    }

    public function testFromReflectionOmitsDefaultValueIfItIsNull(): void
    {
        $reflectionClass    = new ClassReflection(TestAsset\TestClassWithManyProperties::class);
        $propertyReflection = $reflectionClass->getProperty('fooStaticProperty');

        $generator = PropertyGenerator::fromReflection($propertyReflection);
        $code      = $generator->generate();

        $this->assertSame('    public static $fooStaticProperty;', $code);
    }

    public function testFromReflectionWithTypeHintInTypedProperty(): void
    {
        $reflectionProperty = new PropertyReflection(ClassWithTypedProperty::class, 'typedProperty');

        $generator = PropertyGenerator::fromReflection($reflectionProperty);
        $code      = $generator->generate();

        self::assertSame('    private string $typedProperty;', $code);
    }

    public function testFromReflectionReadonlyProperty(): void
    {
        $className = uniqid('ClassWithReadonlyProperty', false);

        eval('namespace ' . __NAMESPACE__ . '; class ' . $className . '{ public readonly string $readonly; }');

        $reflectionProperty = new PropertyReflection(__NAMESPACE__ . '\\' . $className, 'readonly');

        $generator = PropertyGenerator::fromReflection($reflectionProperty);
        $code      = $generator->generate();

        self::assertSame('    public readonly string $readonly;', $code);
    }

    public function testPropertyCanProduceTypeHinting(): void
    {
        $codeGenProperty = new PropertyGenerator('someVal', 'value', [], TypeGenerator::fromTypeString('SomeClass'));
        self::assertSame('    public \SomeClass $someVal = \'value\';', $codeGenProperty->generate());
    }

    public function testFromArrayWithIncorrectTypePassed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/is expecting ' . addslashes(TypeGenerator::class) . '/');

        PropertyGenerator::fromArray([
            'name' => 'someVal',
            'type' => 'invalidStringn',
        ]);
    }

    public function testCanChangeTypeForPropertyGenerator(): void
    {
        $property = new PropertyGenerator('p', null, [], TypeGenerator::fromTypeString('?string'));
        self::assertSame('    public ?string $p = null;', $property->generate());

        $property->setType(null);
        self::assertSame(
            '    public $p = null;',
            $property->generate(),
            'A property type can be dropped'
        );
    }
}
