<?php

namespace LaminasTest\Code\Generator;

use ArrayAccess;
use ArrayObject as SplArrayObject;
use DateTime;
use Generator;
use Laminas\Code\Exception\InvalidArgumentException;
use Laminas\Code\Exception\RuntimeException;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\PropertyValueGenerator;
use Laminas\Code\Generator\ValueGenerator;
use Laminas\Stdlib\ArrayObject as StdlibArrayObject;
use LaminasTest\Code\Generator\TestAsset\TestEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function fopen;
use function str_replace;

#[CoversClass(ValueGenerator::class)]
#[Group('Laminas_Code_Generator')]
#[Group('Laminas_Code_Generator_Php')]
class ValueGeneratorTest extends TestCase
{
    public function testDefaultInstance(): void
    {
        $valueGenerator = new ValueGenerator();

        self::assertInstanceOf(SplArrayObject::class, $valueGenerator->getConstants());
    }

    public function testInvalidConstantsType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$constants must be an instance of ArrayObject or Laminas\Stdlib\ArrayObject');

        $constants = $this->createMock(ArrayAccess::class);
        new ValueGenerator(null, ValueGenerator::TYPE_AUTO, ValueGenerator::OUTPUT_MULTIPLE_LINE, $constants);
    }

    #[DataProvider('constantsType')]
    public function testAllowedPossibleConstantsType(SplArrayObject|StdlibArrayObject $constants): void
    {
        $valueGenerator = new ValueGenerator(
            null,
            ValueGenerator::TYPE_AUTO,
            ValueGenerator::OUTPUT_MULTIPLE_LINE,
            $constants
        );

        self::assertSame($constants, $valueGenerator->getConstants());
    }

    /**
     * @return object[][]
     * @psalm-return array<class-string, array{SplArrayObject|StdlibArrayObject}>
     */
    public static function constantsType(): array
    {
        return [
            SplArrayObject::class    => [new SplArrayObject()],
            StdlibArrayObject::class => [new StdlibArrayObject()],
        ];
    }

    /**
     * @param string $expectedOutput
     */
    #[DataProvider('validConstantTypes')]
    #[Group('#94')]
    public function testValidConstantTypes(PropertyValueGenerator $generator, $expectedOutput): void
    {
        $propertyGenerator = new PropertyGenerator('FOO', $generator);
        $propertyGenerator->setConst(true);
        self::assertSame($expectedOutput, $propertyGenerator->generate());
    }

    /**
     * @psalm-return non-empty-list<array{PropertyValueGenerator, non-empty-string}>
     */
    public static function validConstantTypes(): array
    {
        return [
            [
                new PropertyValueGenerator([], PropertyValueGenerator::TYPE_ARRAY, ValueGenerator::OUTPUT_SINGLE_LINE),
                '    public const FOO = [];',
            ],
            [
                new PropertyValueGenerator(
                    [],
                    PropertyValueGenerator::TYPE_ARRAY_LONG,
                    ValueGenerator::OUTPUT_SINGLE_LINE
                ),
                '    public const FOO = array();',
            ],
            [
                new PropertyValueGenerator(
                    [],
                    PropertyValueGenerator::TYPE_ARRAY_SHORT,
                    ValueGenerator::OUTPUT_SINGLE_LINE
                ),
                '    public const FOO = [];',
            ],
            [new PropertyValueGenerator(true, PropertyValueGenerator::TYPE_BOOL), '    public const FOO = true;'],
            [new PropertyValueGenerator(true, PropertyValueGenerator::TYPE_BOOLEAN), '    public const FOO = true;'],
            [new PropertyValueGenerator(1, PropertyValueGenerator::TYPE_INT), '    public const FOO = 1;'],
            [new PropertyValueGenerator(1, PropertyValueGenerator::TYPE_INTEGER), '    public const FOO = 1;'],
            [new PropertyValueGenerator(0.1, PropertyValueGenerator::TYPE_DOUBLE), '    public const FOO = 0.1;'],
            [new PropertyValueGenerator(0.1, PropertyValueGenerator::TYPE_FLOAT), '    public const FOO = 0.1;'],
            [new PropertyValueGenerator('bar', PropertyValueGenerator::TYPE_STRING), "    public const FOO = 'bar';"],
            [new PropertyValueGenerator(null, PropertyValueGenerator::TYPE_NULL), '    public const FOO = null;'],
            [
                new PropertyValueGenerator('PHP_EOL', PropertyValueGenerator::TYPE_CONSTANT),
                '    public const FOO = PHP_EOL;',
            ],
        ];
    }

    /**
     * @param string $longOutput
     * @return array
     */
    protected static function generateArrayData($longOutput, array $value)
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
                $shortOutput,
            ],
            'array'       => [
                ValueGenerator::TYPE_ARRAY,
                $value,
                $shortOutput,
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
    public static function simpleArray()
    {
        $value = ['foo'];

        $longOutput = <<<EOS
array(
    'foo',
)
EOS;

        return self::generateArrayData($longOutput, $value);
    }

    /**
     * Data provider for testPropertyDefaultValueCanHandleComplexArrayOfTypes test
     *
     * @return array
     */
    public static function complexArray()
    {
        $value = [
            5,
            'one'       => 1,
            'two'       => '2',
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

        return self::generateArrayData($longOutput, $value);
    }

    /**
     * Data provider for testPropertyDefaultValueCanHandleComplexArrayWCustomIndentOfTypes test
     */
    public static function complexArrayWCustomIndent(): array
    {
        $value = [
            '5bcf08a0a5d20' => [
                '5bcf08a0a5d65' => [
                    '5bcf08a0a5d9f' => [
                        '5bcf08a0a5dd8' => [
                            '5bcf08a0a5e11' => [
                                '5bcf08a0a5e4f' => '5bcf08a0a5e8c',
                                '5bcf08a0a5eca' => '5bcf08a0a5f05',
                                '5bcf08a0a5f43' => '5bcf08a0a5f7f',
                                '5bcf08a0a5fbd' => '5bcf08a0a5ff8',
                            ],
                        ],
                        '5bcf08a0a603a' => [],
                        '5bcf08a0a6062' => '5bcf08a0a609f',
                        '5bcf08a0a60dc' => [
                            '5bcf08a0a611b' => '5bcf08a0a6158',
                            '5bcf08a0a6197' => [
                                '5bcf08a0a61d7' => '5bcf08a0a6212',
                                '5bcf08a0a6250' => '5bcf08a0a628c',
                                '5bcf08a0a62cb' => '5bcf08a0a6306',
                            ],
                            '5bcf08a0a6345' => [
                                '5bcf08a0a637e' => '5bcf08a0a63b4',
                                '5bcf08a0a63ee' => '5bcf08a0a642a',
                            ],
                            '5bcf08a0a6449' => '5bcf08a0a6485',
                        ],
                    ],
                ],
                '5bcf08a0a64c8' => '5bcf08a0a6540',
                '5bcf08a0a657f' => '5bcf08a0a65bf',
            ],
        ];

        $longOutput = <<<EOS
array(
	'5bcf08a0a5d20' => array(
		'5bcf08a0a5d65' => array(
			'5bcf08a0a5d9f' => array(
				'5bcf08a0a5dd8' => array(
					'5bcf08a0a5e11' => array(
						'5bcf08a0a5e4f' => '5bcf08a0a5e8c',
						'5bcf08a0a5eca' => '5bcf08a0a5f05',
						'5bcf08a0a5f43' => '5bcf08a0a5f7f',
						'5bcf08a0a5fbd' => '5bcf08a0a5ff8',
					),
				),
				'5bcf08a0a603a' => array(
					
				),
				'5bcf08a0a6062' => '5bcf08a0a609f',
				'5bcf08a0a60dc' => array(
					'5bcf08a0a611b' => '5bcf08a0a6158',
					'5bcf08a0a6197' => array(
						'5bcf08a0a61d7' => '5bcf08a0a6212',
						'5bcf08a0a6250' => '5bcf08a0a628c',
						'5bcf08a0a62cb' => '5bcf08a0a6306',
					),
					'5bcf08a0a6345' => array(
						'5bcf08a0a637e' => '5bcf08a0a63b4',
						'5bcf08a0a63ee' => '5bcf08a0a642a',
					),
					'5bcf08a0a6449' => '5bcf08a0a6485',
				),
			),
		),
		'5bcf08a0a64c8' => '5bcf08a0a6540',
		'5bcf08a0a657f' => '5bcf08a0a65bf',
	),
)
EOS;

        return self::generateArrayData($longOutput, $value);
    }

    /**
     * Data provider for testPropertyDefaultValueCanHandleArrayWithUnsortedKeys test
     *
     * @return array
     */
    public static function unsortedKeysArray()
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

        return self::generateArrayData($longOutput, $value);
    }

    /**
     * @param string $type
     * @param string $expected
     */
    #[DataProvider('unsortedKeysArray')]
    public function testPropertyDefaultValueCanHandleArrayWithUnsortedKeys($type, array $value, $expected)
    {
        $valueGenerator = new ValueGenerator();
        $valueGenerator->setType($type);
        $valueGenerator->setValue($value);

        self::assertSame($expected, $valueGenerator->generate());
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
        self::assertSame('foo', $valueGenerator->getValue());
    }

    public function testPropertyDefaultValueCanHandleStrings()
    {
        $valueGenerator = new ValueGenerator();
        $valueGenerator->setValue('foo');
        self::assertSame("'foo'", $valueGenerator->generate());
    }

    public function testPropertyDefaultValueCanHandleBool()
    {
        $valueGenerator1 = new ValueGenerator(
            'FALSE',
            ValueGenerator::TYPE_AUTO,
            ValueGenerator::OUTPUT_MULTIPLE_LINE
        );

        $valueGenerator2 = new ValueGenerator(
            'FALSE',
            ValueGenerator::TYPE_STRING,
            ValueGenerator::OUTPUT_MULTIPLE_LINE
        );

        $valueGenerator1->initEnvironmentConstants();
        $valueGenerator2->initEnvironmentConstants();

        self::assertNotEquals($valueGenerator1->generate(), $valueGenerator2->generate());
    }

    public function testPropertyDefaultValueCanHandleEnums(): void
    {
        $valueGenerator1 = new ValueGenerator(
            TestEnum::Test1,
            ValueGenerator::TYPE_AUTO,
            ValueGenerator::OUTPUT_MULTIPLE_LINE
        );

        $valueGenerator2 = new ValueGenerator(TestEnum::Test2);

        self::assertSame('\LaminasTest\Code\Generator\TestAsset\TestEnum::Test1', $valueGenerator1->generate());
        self::assertSame('\LaminasTest\Code\Generator\TestAsset\TestEnum::Test2', $valueGenerator2->generate());
    }

    /**
     * @param string $type
     * @param string $expected
     */
    #[DataProvider('simpleArray')]
    public function testPropertyDefaultValueCanHandleArray($type, array $value, $expected)
    {
        $valueGenerator = new ValueGenerator();
        $valueGenerator->setType($type);
        $valueGenerator->setValue($value);

        self::assertSame($expected, $valueGenerator->generate());
    }

    public function testPropertyDefaultValueCanHandleUnquotedString()
    {
        $valueGenerator = new ValueGenerator();
        $valueGenerator->setValue('PHP_EOL');
        $valueGenerator->setType('constant');
        self::assertSame('PHP_EOL', $valueGenerator->generate());

        $valueGenerator = new ValueGenerator();
        $valueGenerator->setValue(5);
        self::assertSame('5', $valueGenerator->generate());

        $valueGenerator = new ValueGenerator();
        $valueGenerator->setValue(5.25);
        self::assertSame('5.25', $valueGenerator->generate());
    }

    /**
     * @param string $type
     * @param string $expected
     */
    #[DataProvider('complexArray')]
    public function testPropertyDefaultValueCanHandleComplexArrayOfTypes($type, array $value, $expected)
    {
        $valueGenerator = new ValueGenerator();
        $valueGenerator->initEnvironmentConstants();
        $valueGenerator->setType($type);
        $valueGenerator->setValue($value);

        self::assertSame($expected, $valueGenerator->generate());
    }

    #[DataProvider('complexArrayWCustomIndent')]
    public function testPropertyDefaultValueCanHandleComplexArrayWCustomIndentOfTypes(
        string $type,
        array $value,
        string $expected
    ): void {
        $valueGenerator = new ValueGenerator();
        $valueGenerator->setType($type);
        $valueGenerator->setValue($value);
        $valueGenerator->setIndentation("\t");

        self::assertSame($expected, $valueGenerator->generate());
    }

    /**
     * @param string $input
     * @param string $expectedEscapedValue
     */
    #[DataProvider('getEscapedParameters')]
    #[Group('6023')]
    public function testEscaping($input, $expectedEscapedValue)
    {
        self::assertSame($expectedEscapedValue, ValueGenerator::escape($input, false));
    }

    /**
     * Data provider for escaping tests
     *
     * @return string[][]
     */
    public static function getEscapedParameters()
    {
        return [
            ['\\', '\\\\'],
            ["'", "\\'"],
            ["\\'", "\\\\\\'"],
        ];
    }

    public static function invalidValue(): Generator
    {
        yield 'object' => [new DateTime(), DateTime::class];
        yield 'resource' => [fopen('php://input', 'r'), 'resource (stream)'];
    }

    #[DataProvider('invalidValue')]
    public function testExceptionInvalidValue(mixed $value, string $type): void
    {
        $valueGenerator = new ValueGenerator($value);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Type "' . $type . '" is unknown or cannot be used');
        $valueGenerator->generate();
    }

    /**
     * @param ValueGenerator::OUTPUT_* $outputMode
     */
    #[DataProvider('multipleOutputArray')]
    public function testArrayWithOutputMode(
        array $array,
        string $type,
        string $outputMode,
        string $output
    ): void {
        $valueGenerator = new ValueGenerator($array, $type, $outputMode);

        self::assertSame($valueGenerator->generate(), $output);
    }

    /**
     * Data provider for testArrayWithOutputMode test
     */
    public static function multipleOutputArray(): array
    {
        $array = [
            'foo' => [
                'bar',
            ],
        ];

        $singleLine   = '[\'foo\' => [\'bar\']]';
        $multipleLine = <<<EOS
[
    'foo' => [
        'bar',
    ],
]
EOS;

        return [
            'singleLine'   => [
                $array,
                ValueGenerator::TYPE_ARRAY_SHORT,
                ValueGenerator::OUTPUT_SINGLE_LINE,
                $singleLine,
            ],
            'multipleLine' => [
                $array,
                ValueGenerator::TYPE_ARRAY_SHORT,
                ValueGenerator::OUTPUT_MULTIPLE_LINE,
                $multipleLine,
            ],
        ];
    }
}
