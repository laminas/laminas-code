<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator;

use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\PropertyValueGenerator;
use Laminas\Code\Reflection\ClassReflection;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class PropertyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testPropertyConstructor()
    {
        $codeGenProperty = new PropertyGenerator();
        $this->isInstanceOf($codeGenProperty, 'Laminas\Code\Generator\PropertyGenerator');
    }

    /**
     * @return array
     */
    public function dataSetTypeSetValueGenerate()
    {
        return [
            ['string', 'foo', "'foo';"],
            ['int', 1, "1;"],
            ['integer', 1, "1;"],
            ['bool', true, "true;"],
            ['bool', false, "false;"],
            ['boolean', true, "true;"],
            ['number', 1, '1;'],
            ['float', 1.23, '1.23;'],
            ['double', 1.23, '1.23;'],
            ['constant', 'FOO', 'FOO;'],
            ['null', null, 'null;'],
        ];
    }

    /**
     * @dataProvider dataSetTypeSetValueGenerate
     * @param string $type
     * @param mixed $value
     * @param string $code
     */
    public function testSetTypeSetValueGenerate($type, $value, $code)
    {
        $defaultValue = new PropertyValueGenerator();
        $defaultValue->setType($type);
        $defaultValue->setValue($value);

        $this->assertEquals($type, $defaultValue->getType());
        $this->assertEquals($code, $defaultValue->generate());
    }

    /**
     * @dataProvider dataSetTypeSetValueGenerate
     * @param string $type
     * @param mixed $value
     * @param string $code
     */
    public function testSetBogusTypeSetValueGenerateUseAutoDetection($type, $value, $code)
    {
        if ($type == 'constant') {
            return; // constant can only be detected explicitly
        }

        $defaultValue = new PropertyValueGenerator();
        $defaultValue->setType("bogus");
        $defaultValue->setValue($value);

        $this->assertEquals($code, $defaultValue->generate());
    }

    public function testPropertyReturnsSimpleValue()
    {
        $codeGenProperty = new PropertyGenerator('someVal', 'some string value');
        $this->assertEquals('    public $someVal = \'some string value\';', $codeGenProperty->generate());
    }

    public function testPropertyMultilineValue()
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

        $this->assertEquals($expectedSource, $targetSource);
    }

    public function testPropertyCanProduceContstantModifier()
    {
        $codeGenProperty = new PropertyGenerator('someVal', 'some string value', PropertyGenerator::FLAG_CONSTANT);
        $this->assertEquals('    const someVal = \'some string value\';', $codeGenProperty->generate());
    }

    /**
     * @group PR-704
     */
    public function testPropertyCanProduceContstantModifierWithSetter()
    {
        $codeGenProperty = new PropertyGenerator('someVal', 'some string value');
        $codeGenProperty->setConst(true);
        $this->assertEquals('    const someVal = \'some string value\';', $codeGenProperty->generate());
    }

    public function testPropertyCanProduceStaticModifier()
    {
        $codeGenProperty = new PropertyGenerator('someVal', 'some string value', PropertyGenerator::FLAG_STATIC);
        $this->assertEquals('    public static $someVal = \'some string value\';', $codeGenProperty->generate());
    }

    /**
     * @group Laminas-6444
     */
    public function testPropertyWillLoadFromReflection()
    {
        $reflectionClass = new \Laminas\Code\Reflection\ClassReflection(
            '\LaminasTest\Code\Generator\TestAsset\TestClassWithManyProperties'
        );

        // test property 1
        $reflProp = $reflectionClass->getProperty('_bazProperty');

        $cgProp = PropertyGenerator::fromReflection($reflProp);

        $this->assertEquals('_bazProperty', $cgProp->getName());
        $this->assertEquals([true, false, true], $cgProp->getDefaultValue()->getValue());
        $this->assertEquals('private', $cgProp->getVisibility());

        $reflProp = $reflectionClass->getProperty('_bazStaticProperty');

        // test property 2
        $cgProp = PropertyGenerator::fromReflection($reflProp);

        $this->assertEquals('_bazStaticProperty', $cgProp->getName());
        $this->assertEquals(TestAsset\TestClassWithManyProperties::FOO, $cgProp->getDefaultValue()->getValue());
        $this->assertTrue($cgProp->isStatic());
        $this->assertEquals('private', $cgProp->getVisibility());
    }

    /**
     * @group Laminas-6444
     */
    public function testPropertyWillEmitStaticModifier()
    {
        $codeGenProperty = new PropertyGenerator(
            'someVal',
            'some string value',
            PropertyGenerator::FLAG_STATIC | PropertyGenerator::FLAG_PROTECTED
        );
        $this->assertEquals('    protected static $someVal = \'some string value\';', $codeGenProperty->generate());
    }

    /**
     * @group Laminas-7205
     */
    public function testPropertyCanHaveDocBlock()
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
        $this->assertEquals($expected, $codeGenProperty->generate());
    }

    public function testOtherTypesThrowExceptionOnGenerate()
    {
        $codeGenProperty = new PropertyGenerator('someVal', new \stdClass());

        $this->setExpectedException(
            'Laminas\Code\Generator\Exception\RuntimeException',
            'Type "stdClass" is unknown or cannot be used as property default value'
        );

        $codeGenProperty->generate();
    }

    public function testCreateFromArray()
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

        $this->assertEquals('SampleProperty', $propertyGenerator->getName());
        $this->assertTrue($propertyGenerator->isConst());
        $this->assertInstanceOf('Laminas\Code\Generator\ValueGenerator', $propertyGenerator->getDefaultValue());
        $this->assertInstanceOf('Laminas\Code\Generator\DocBlockGenerator', $propertyGenerator->getDocBlock());
        $this->assertTrue($propertyGenerator->isAbstract());
        $this->assertTrue($propertyGenerator->isFinal());
        $this->assertTrue($propertyGenerator->isStatic());
        $this->assertEquals(PropertyGenerator::VISIBILITY_PROTECTED, $propertyGenerator->getVisibility());
    }

    /**
     * @3491
     */
    public function testPropertyDocBlockWillLoadFromReflection()
    {
        $reflectionClass = new ClassReflection(TestAsset\TestClassWithManyProperties::class);

        $reflProp = $reflectionClass->getProperty('fooProperty');
        $cgProp   = PropertyGenerator::fromReflection($reflProp);

        $this->assertEquals('fooProperty', $cgProp->getName());

        $docBlock = $cgProp->getDocBlock();
        $this->assertInstanceOf('Laminas\Code\Generator\DocBlockGenerator', $docBlock);
        $tags     = $docBlock->getTags();
        $this->assertInternalType('array', $tags);
        $this->assertEquals(1, count($tags));
        $tag = array_shift($tags);
        $this->assertInstanceOf('Laminas\Code\Generator\DocBlock\Tag\GenericTag', $tag);
        $this->assertEquals('var', $tag->getName());
    }


    /**
     * @dataProvider dataSetTypeSetValueGenerate
     * @param string $type
     * @param mixed $value
     * @param string $code
     */
    public function testSetDefaultValue($type, $value, $code)
    {
        $property = new PropertyGenerator();
        $property->setDefaultValue($value, $type);

        $this->assertEquals($type, $property->getDefaultValue()->getType());
        $this->assertEquals($value, $property->getDefaultValue()->getValue());
    }
}
