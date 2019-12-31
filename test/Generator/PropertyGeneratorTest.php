<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator;

use Laminas\Code\Generator\DocBlock\Tag\VarTag;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\Exception\RuntimeException;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\PropertyValueGenerator;
use Laminas\Code\Generator\ValueGenerator;
use Laminas\Code\Reflection\ClassReflection;
use PHPUnit\Framework\TestCase;

use function array_shift;
use function str_replace;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class PropertyGeneratorTest extends TestCase
{
    public function testPropertyConstructor() : void
    {
        $codeGenProperty = new PropertyGenerator();
        self::assertInstanceOf(PropertyGenerator::class, $codeGenProperty);
    }

    /**
     * @return bool[][]|string[][]|int[][]|null[][]
     */
    public function dataSetTypeSetValueGenerate() : array
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

    /**
     * @dataProvider dataSetTypeSetValueGenerate
     *
     * @param string $type
     * @param mixed $value
     * @param string $code
     */
    public function testSetTypeSetValueGenerate(string $type, $value, string $code) : void
    {
        $defaultValue = new PropertyValueGenerator();
        $defaultValue->setType($type);
        $defaultValue->setValue($value);

        self::assertEquals($type, $defaultValue->getType());
        self::assertEquals($code, $defaultValue->generate());
    }

    /**
     * @dataProvider dataSetTypeSetValueGenerate
     *
     * @param string $type
     * @param mixed $value
     * @param string $code
     */
    public function testSetBogusTypeSetValueGenerateUseAutoDetection(string $type, $value, string $code) : void
    {
        if ('constant' === $type) {
            self::markTestSkipped('constant can only be detected explicitly');
        }

        $defaultValue = new PropertyValueGenerator();
        $defaultValue->setType('bogus');
        $defaultValue->setValue($value);

        self::assertEquals($code, $defaultValue->generate());
    }

    public function testPropertyReturnsSimpleValue() : void
    {
        $codeGenProperty = new PropertyGenerator('someVal', 'some string value');
        self::assertEquals('    public $someVal = \'some string value\';', $codeGenProperty->generate());
    }

    public function testPropertyMultilineValue() : void
    {
        $targetValue = [
            5,
            'one' => 1,
            'two' => '2',
            'null' => null,
            'true' => true,
            "bar's" => "bar's",
        ];

        $expectedSource = <<<EOS
    public \$myFoo = array(
        5,
        'one' => 1,
        'two' => '2',
        'null' => null,
        'true' => true,
        'bar\'s' => 'bar\'s',
    );
EOS;

        $property = new PropertyGenerator('myFoo', $targetValue);

        $targetSource = $property->generate();
        $targetSource = str_replace("\r", '', $targetSource);

        self::assertEquals($expectedSource, $targetSource);
    }

    public function testPropertyCanProduceContstantModifier() : void
    {
        $codeGenProperty = new PropertyGenerator('someVal', 'some string value', PropertyGenerator::FLAG_CONSTANT);
        self::assertEquals('    const someVal = \'some string value\';', $codeGenProperty->generate());
    }

    /**
     * @group PR-704
     */
    public function testPropertyCanProduceContstantModifierWithSetter() : void
    {
        $codeGenProperty = new PropertyGenerator('someVal', 'some string value');
        $codeGenProperty->setConst(true);
        self::assertEquals('    const someVal = \'some string value\';', $codeGenProperty->generate());
    }

    public function testPropertyCanProduceStaticModifier() : void
    {
        $codeGenProperty = new PropertyGenerator('someVal', 'some string value', PropertyGenerator::FLAG_STATIC);
        self::assertEquals('    public static $someVal = \'some string value\';', $codeGenProperty->generate());
    }

    /**
     * @group Laminas-6444
     */
    public function testPropertyWillLoadFromReflection() : void
    {
        $reflectionClass = new ClassReflection(TestAsset\TestClassWithManyProperties::class);

        // test property 1
        $reflProp = $reflectionClass->getProperty('_bazProperty');

        $cgProp = PropertyGenerator::fromReflection($reflProp);

        self::assertEquals('_bazProperty', $cgProp->getName());
        self::assertEquals([true, false, true], $cgProp->getDefaultValue()->getValue());
        self::assertEquals('private', $cgProp->getVisibility());

        $reflProp = $reflectionClass->getProperty('_bazStaticProperty');

        // test property 2
        $cgProp = PropertyGenerator::fromReflection($reflProp);

        self::assertEquals('_bazStaticProperty', $cgProp->getName());
        self::assertEquals(TestAsset\TestClassWithManyProperties::FOO, $cgProp->getDefaultValue()->getValue());
        self::assertTrue($cgProp->isStatic());
        self::assertEquals('private', $cgProp->getVisibility());
    }

    /**
     * @group Laminas-6444
     */
    public function testPropertyWillEmitStaticModifier() : void
    {
        $codeGenProperty = new PropertyGenerator(
            'someVal',
            'some string value',
            PropertyGenerator::FLAG_STATIC | PropertyGenerator::FLAG_PROTECTED
        );
        self::assertEquals('    protected static $someVal = \'some string value\';', $codeGenProperty->generate());
    }

    /**
     * @group Laminas-7205
     */
    public function testPropertyCanHaveDocBlock() : void
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
        self::assertEquals($expected, $codeGenProperty->generate());
    }

    public function testOtherTypesThrowExceptionOnGenerate() : void
    {
        $codeGenProperty = new PropertyGenerator('someVal', new \stdClass());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Type "stdClass" is unknown or cannot be used as property default value');

        $codeGenProperty->generate();
    }

    public function testCreateFromArray() : void
    {
        $propertyGenerator = PropertyGenerator::fromArray([
            'name'         => 'SampleProperty',
            'const'        => true,
            'defaultvalue' => 'foo',
            'docblock'     => [
                'shortdescription' => 'foo',
            ],
            'abstract'     => true,
            'final'        => true,
            'static'       => true,
            'visibility'   => PropertyGenerator::VISIBILITY_PROTECTED,
        ]);

        self::assertEquals('SampleProperty', $propertyGenerator->getName());
        self::assertTrue($propertyGenerator->isConst());
        self::assertInstanceOf(ValueGenerator::class, $propertyGenerator->getDefaultValue());
        self::assertInstanceOf(DocBlockGenerator::class, $propertyGenerator->getDocBlock());
        self::assertTrue($propertyGenerator->isAbstract());
        self::assertTrue($propertyGenerator->isFinal());
        self::assertTrue($propertyGenerator->isStatic());
        self::assertEquals(PropertyGenerator::VISIBILITY_PROTECTED, $propertyGenerator->getVisibility());
    }

    /**
     * @group 3491
     */
    public function testPropertyDocBlockWillLoadFromReflection() : void
    {
        $reflectionClass = new ClassReflection(TestAsset\TestClassWithManyProperties::class);

        $reflProp = $reflectionClass->getProperty('fooProperty');
        $cgProp   = PropertyGenerator::fromReflection($reflProp);

        self::assertEquals('fooProperty', $cgProp->getName());

        $docBlock = $cgProp->getDocBlock();
        self::assertInstanceOf(DocBlockGenerator::class, $docBlock);
        $tags     = $docBlock->getTags();
        self::assertInternalType('array', $tags);
        self::assertCount(1, $tags);
        $tag = array_shift($tags);
        self::assertInstanceOf(VarTag::class, $tag);
        self::assertEquals('var', $tag->getName());
    }

    /**
     * @dataProvider dataSetTypeSetValueGenerate
     *
     * @param string $type
     * @param mixed $value
     */
    public function testSetDefaultValue(string $type, $value) : void
    {
        $property = new PropertyGenerator();
        $property->setDefaultValue($value, $type);

        self::assertEquals($type, $property->getDefaultValue()->getType());
        self::assertEquals($value, $property->getDefaultValue()->getValue());
    }
}
