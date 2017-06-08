<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Generator;

use ArrayAccess;
use ArrayObject as SplArrayObject;
use PHPUnit\Framework\TestCase;
use Zend\Code\Exception\InvalidArgumentException;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\PropertyValueGenerator;
use Zend\Code\Generator\ValueGenerator;
use Zend\Stdlib\ArrayObject as StdlibArrayObject;

/**
 * @group Zend_Code_Generator
 * @group Zend_Code_Generator_Php
 *
 * @covers \Zend\Code\Generator\ValueGenerator
 */
class ValueGeneratorTest extends TestCase
{
    public function testDefaultInstance()
    {
        $valueGenerator = new ValueGenerator();

        self::assertInstanceOf(SplArrayObject::class, $valueGenerator->getConstants());
    }

    public function testInvalidConstantsType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$constants must be an instance of ArrayObject or Zend\Stdlib\ArrayObject');

        $constants = $this->createMock(ArrayAccess::class);
        new ValueGenerator(null, ValueGenerator::TYPE_AUTO, ValueGenerator::OUTPUT_MULTIPLE_LINE, $constants);
    }

    /**
     * @dataProvider constantsType
     *
     * @param SplArrayObject|StdlibArrayObject $constants
     */
    public function testAllowedPossibleConstantsType($constants)
    {
        $valueGenerator = new ValueGenerator(
            null,
            ValueGenerator::TYPE_AUTO,
            ValueGenerator::OUTPUT_MULTIPLE_LINE,
            $constants
        );

        self::assertSame($constants, $valueGenerator->getConstants());
    }

    public function constantsType()
    {
        return [
            SplArrayObject::class => [new SplArrayObject()],
            StdlibArrayObject::class => [new StdlibArrayObject()],
        ];
    }

    /**
     * @group #94
     *
     * @dataProvider validConstantTypes
     *
     * @param PropertyValueGenerator $generator
     * @param string $expectedOutput
     */
    public function testValidConstantTypes(PropertyValueGenerator $generator, $expectedOutput)
    {
        $propertyGenerator = new PropertyGenerator('FOO', $generator);
        $propertyGenerator->setConst(true);
        self::assertSame($expectedOutput, $propertyGenerator->generate());
    }

    /**
     * @return array
     */
    public function validConstantTypes()
    {
        return [
            [
                new PropertyValueGenerator([], PropertyValueGenerator::TYPE_ARRAY, ValueGenerator::OUTPUT_SINGLE_LINE),
                '    const FOO = array();',
            ],
            [
                new PropertyValueGenerator(
                    [],
                    PropertyValueGenerator::TYPE_ARRAY_LONG,
                    ValueGenerator::OUTPUT_SINGLE_LINE
                ),
                '    const FOO = array();',
            ],
            [
                new PropertyValueGenerator(
                    [],
                    PropertyValueGenerator::TYPE_ARRAY_SHORT,
                    ValueGenerator::OUTPUT_SINGLE_LINE
                ),
                '    const FOO = [];',
            ],
            [new PropertyValueGenerator(true, PropertyValueGenerator::TYPE_BOOL), '    const FOO = true;'],
            [new PropertyValueGenerator(true, PropertyValueGenerator::TYPE_BOOLEAN), '    const FOO = true;'],
            [new PropertyValueGenerator(1, PropertyValueGenerator::TYPE_INT), '    const FOO = 1;'],
            [new PropertyValueGenerator(1, PropertyValueGenerator::TYPE_INTEGER), '    const FOO = 1;'],
            [new PropertyValueGenerator(0.1, PropertyValueGenerator::TYPE_DOUBLE), '    const FOO = 0.1;'],
            [new PropertyValueGenerator(0.1, PropertyValueGenerator::TYPE_FLOAT), '    const FOO = 0.1;'],
            [new PropertyValueGenerator('bar', PropertyValueGenerator::TYPE_STRING), "    const FOO = 'bar';"],
            [new PropertyValueGenerator(null, PropertyValueGenerator::TYPE_NULL), '    const FOO = null;'],
            [new PropertyValueGenerator('PHP_EOL', PropertyValueGenerator::TYPE_CONSTANT), '    const FOO = PHP_EOL;'],
        ];
    }

    /**
     * @param string $longOutput
     * @param array $value
     * @return array
     */
    protected function generateArrayData($longOutput, array $value)
    {
        $shortOutput = str_replace(
            ['array(', ')'],
            ['[', ']'],
            $longOutput
        );

        return [
            'auto'        => [
                ValueGenerator::TYPE_AUTO,
                $value,
                $longOutput,
            ],
            'array'       => [
                ValueGenerator::TYPE_ARRAY,
                $value,
                $longOutput,
            ],
            'array long'  => [
                ValueGenerator::TYPE_ARRAY_LONG,
                $value,
                $longOutput,
            ],
            'array short' => [
                ValueGenerator::TYPE_ARRAY_SHORT,
                $value,
                $shortOutput,
            ],
        ];
    }

    /**
     * Data provider for testPropertyDefaultValueCanHandleArray test
     *
     * @return array
     */
    public function simpleArray()
    {
        $value = ['foo'];

        $longOutput = <<<EOS
array(
    'foo',
)
EOS;

        return $this->generateArrayData($longOutput, $value);
    }

    /**
     * Data provider for testPropertyDefaultValueCanHandleComplexArrayOfTypes test
     *
     * @return array
     */
    public function complexArray()
    {
        $value = [
            5,
            'one' => 1,
            'two' => '2',
            'constant1' => "__DIR__ . '/anydir1/anydir2'",
            [
                'baz' => true,
                'foo',
                'bar',
                [
                    'baz1',
                    'baz2',
                    'constant2' => 'ArrayObject::STD_PROP_LIST',
                ],
            ],
            new ValueGenerator('PHP_EOL', 'constant'),
        ];

        $longOutput = <<<EOS
array(
    5,
    'one' => 1,
    'two' => '2',
    'constant1' => __DIR__ . '/anydir1/anydir2',
    array(
        'baz' => true,
        'foo',
        'bar',
        array(
            'baz1',
            'baz2',
            'constant2' => ArrayObject::STD_PROP_LIST,
        ),
    ),
    PHP_EOL,
)
EOS;

        return $this->generateArrayData($longOutput, $value);
    }

    /**
     * Data provider for testPropertyDefaultValueCanHandleArrayWithUnsortedKeys test
     *
     * @return array
     */
    public function unsortedKeysArray()
    {
        $value = [
            1 => 'a',
            0 => 'b',
            'c',
            7 => 'd',
            3 => 'e',
        ];

        $longOutput = <<<EOS
array(
    1 => 'a',
    0 => 'b',
    'c',
    7 => 'd',
    3 => 'e',
)
EOS;

        return $this->generateArrayData($longOutput, $value);
    }

    /**
     * @dataProvider unsortedKeysArray
     *
     * @param string $type
     * @param array $value
     * @param string $expected
     */
    public function testPropertyDefaultValueCanHandleArrayWithUnsortedKeys($type, array $value, $expected)
    {
        $valueGenerator = new ValueGenerator();
        $valueGenerator->setType($type);
        $valueGenerator->setValue($value);

        self::assertEquals($expected, $valueGenerator->generate());
    }

    public function testPropertyDefaultValueConstructor()
    {
        $valueGenerator = new ValueGenerator();
        self::assertInstanceOf(ValueGenerator::class, $valueGenerator);
    }

    public function testPropertyDefaultValueIsSettable()
    {
        $valueGenerator = new ValueGenerator();
        $valueGenerator->setValue('foo');
        self::assertEquals('foo', $valueGenerator->getValue());
    }

    public function testPropertyDefaultValueCanHandleStrings()
    {
        $valueGenerator = new ValueGenerator();
        $valueGenerator->setValue('foo');
        self::assertEquals("'foo'", $valueGenerator->generate());
    }

    /**
     * @dataProvider simpleArray
     *
     * @param string $type
     * @param array $value
     * @param string $expected
     */
    public function testPropertyDefaultValueCanHandleArray($type, array $value, $expected)
    {
        $valueGenerator = new ValueGenerator();
        $valueGenerator->setType($type);
        $valueGenerator->setValue($value);

        self::assertEquals($expected, $valueGenerator->generate());
    }

    public function testPropertyDefaultValueCanHandleUnquotedString()
    {
        $valueGenerator = new ValueGenerator();
        $valueGenerator->setValue('PHP_EOL');
        $valueGenerator->setType('constant');
        self::assertEquals('PHP_EOL', $valueGenerator->generate());

        $valueGenerator = new ValueGenerator();
        $valueGenerator->setValue(5);
        self::assertEquals('5', $valueGenerator->generate());

        $valueGenerator = new ValueGenerator();
        $valueGenerator->setValue(5.25);
        self::assertEquals('5.25', $valueGenerator->generate());
    }

    /**
     * @dataProvider complexArray
     *
     * @param string $type
     * @param array $value
     * @param string $expected
     */
    public function testPropertyDefaultValueCanHandleComplexArrayOfTypes($type, array $value, $expected)
    {
        $valueGenerator = new ValueGenerator();
        $valueGenerator->initEnvironmentConstants();
        $valueGenerator->setType($type);
        $valueGenerator->setValue($value);

        self::assertEquals($expected, $valueGenerator->generate());
    }

    /**
     * @group 6023
     *
     * @dataProvider getEscapedParameters
     *
     * @param string $input
     * @param string $expectedEscapedValue
     */
    public function testEscaping($input, $expectedEscapedValue)
    {
        self::assertSame($expectedEscapedValue, ValueGenerator::escape($input, false));
    }

    /**
     * Data provider for escaping tests
     *
     * @return string[][]
     */
    public function getEscapedParameters()
    {
        return [
            ['\\', '\\\\'],
            ["'", "\\'"],
            ["\\'", "\\\\\\'"],
        ];
    }
}
