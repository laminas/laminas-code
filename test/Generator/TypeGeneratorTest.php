<?php

/**
 * @see       https://github.com/laminasframwork/laminas-code for the canonical source repository
 * @copyright https://github.com/laminasframwork/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminasframwork/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator;

use Laminas\Code\Exception\InvalidArgumentException;
use Laminas\Code\Generator\GeneratorInterface;
use Laminas\Code\Generator\TypeGenerator;
use PHPUnit\Framework\TestCase;

use function array_combine;
use function array_filter;
use function array_map;
use function class_implements;
use function ltrim;
use function strpos;

/**
 * @group zendframework/zend-code#29
 *
 * @covers \Laminas\Code\Generator\TypeGenerator
 */
class TypeGeneratorTest extends TestCase
{
    public function testIsAGenerator()
    {
        self::assertContains(GeneratorInterface::class, class_implements(TypeGenerator::class));
    }

    /**
     * @dataProvider validType
     *
     * @param string $typeString
     * @param string $expectedReturnType
     */
    public function testFromValidTypeString($typeString, $expectedReturnType)
    {
        $generator = TypeGenerator::fromTypeString($typeString);

        self::assertSame($expectedReturnType, $generator->generate());
    }

    /**
     * @dataProvider validType
     *
     * @param string $typeString
     * @param string $expectedReturnType
     */
    public function testStringCastFromValidTypeString($typeString, $expectedReturnType)
    {
        $generator = TypeGenerator::fromTypeString($typeString);

        self::assertSame(ltrim($expectedReturnType, '?\\'), (string) $generator);
    }

    /**
     * @dataProvider validClassName
     *
     * @param string $typeString
     * @param string $expectedReturnType
     */
    public function testStripsPrefixingBackslashFromClassNames($typeString, $expectedReturnType)
    {
        $generator = TypeGenerator::fromTypeString('\\' . $typeString);

        self::assertSame($expectedReturnType, $generator->generate());
        self::assertSame(ltrim($expectedReturnType, '\\'), (string) $generator);
    }

    /**
     * @dataProvider invalidType
     *
     * @param string $typeString
     */
    public function testRejectsInvalidTypeString($typeString)
    {
        $this->expectException(InvalidArgumentException::class);

        TypeGenerator::fromTypeString($typeString);
    }

    /**
     * @return string[][]
     */
    public function validType()
    {
        $valid = [
            ['foo', '\\foo'],
            ['foo', '\\foo'],
            ['foo1', '\\foo1'],
            ['foo\\bar', '\\foo\\bar'],
            ['a\\b\\c', '\\a\\b\\c'],
            ['foo\\bar\\baz', '\\foo\\bar\\baz'],
            ['foo\\bar\\baz1', '\\foo\\bar\\baz1'],
            ['FOO', '\\FOO'],
            ['FOO1', '\\FOO1'],
            ['void', 'void'],
            ['Void', 'void'],
            ['VOID', 'void'],
            ['array', 'array'],
            ['Array', 'array'],
            ['ARRAY', 'array'],
            ['callable', 'callable'],
            ['Callable', 'callable'],
            ['CALLABLE', 'callable'],
            ['string', 'string'],
            ['String', 'string'],
            ['STRING', 'string'],
            ['int', 'int'],
            ['Int', 'int'],
            ['INT', 'int'],
            ['float', 'float'],
            ['Float', 'float'],
            ['FLOAT', 'float'],
            ['bool', 'bool'],
            ['Bool', 'bool'],
            ['BOOL', 'bool'],
            ['iterable', 'iterable'],
            ['Iterable', 'iterable'],
            ['ITERABLE', 'iterable'],
            ['object', 'object'],
            ['Object', 'object'],
            ['OBJECT', 'object'],
            ['mixed', '\\mixed'],
            ['Mixed', '\\Mixed'],
            ['MIXED', '\\MIXED'],
            ['resource', '\\resource'],
            ['Resource', '\\Resource'],
            ['RESOURCE', '\\RESOURCE'],
            ['foo_bar', '\\foo_bar'],
            ['?foo', '?\\foo'],
            ['?foo', '?\\foo'],
            ['?foo1', '?\\foo1'],
            ['?foo\\bar', '?\\foo\\bar'],
            ['?a\\b\\c', '?\\a\\b\\c'],
            ['?foo\\bar\\baz', '?\\foo\\bar\\baz'],
            ['?foo\\bar\\baz1', '?\\foo\\bar\\baz1'],
            ['?FOO', '?\\FOO'],
            ['?FOO1', '?\\FOO1'],
            ['?array', '?array'],
            ['?Array', '?array'],
            ['?ARRAY', '?array'],
            ['?callable', '?callable'],
            ['?Callable', '?callable'],
            ['?CALLABLE', '?callable'],
            ['?string', '?string'],
            ['?String', '?string'],
            ['?STRING', '?string'],
            ['?int', '?int'],
            ['?Int', '?int'],
            ['?INT', '?int'],
            ['?float', '?float'],
            ['?Float', '?float'],
            ['?FLOAT', '?float'],
            ['?bool', '?bool'],
            ['?Bool', '?bool'],
            ['?BOOL', '?bool'],
            ['?iterable', '?iterable'],
            ['?Iterable', '?iterable'],
            ['?ITERABLE', '?iterable'],
            ['?object', '?object'],
            ['?Object', '?object'],
            ['?OBJECT', '?object'],
            ['?mixed', '?\\mixed'],
            ['?Mixed', '?\\Mixed'],
            ['?MIXED', '?\\MIXED'],
            ['?resource', '?\\resource'],
            ['?Resource', '?\\Resource'],
            ['?RESOURCE', '?\\RESOURCE'],
            ['?foo_bar', '?\\foo_bar'],
            ["\x80", "\\\x80"],
            ["\x80\\\x80", "\\\x80\\\x80"],
        ];

        return array_combine(
            array_map('reset', $valid),
            $valid
        );
    }

    /**
     * Valid class names - just the same as validType, but with only those elements prefixed by '\\'
     *
     * @return string[][]
     */
    public function validClassName()
    {
        return array_filter(
            $this->validType(),
            function (array $pair) {
                return 0 === strpos($pair[1], '\\');
            }
        );
    }

    /**
     * @return string[][]
     */
    public function invalidType()
    {
        $invalid = [
            [''],
            ['\\'],
            ['\\\\'],
            ['\\\\foo'],
            ["\x7f"],
            ["foo\\\x7f"],
            ["foo\x7f\\foo"],
            ['1'],
            ['\\1'],
            ['\\1\\2'],
            ['foo\\1'],
            ['foo\\bar\\1'],
            ['1foo'],
            ['foo\\1foo'],
            ['*'],
            ["\0"],
            ['\\array'],
            ['\\Array'],
            ['\\ARRAY'],
            ['\\callable'],
            ['\\Callable'],
            ['\\CALLABLE'],
            ['\\string'],
            ['\\String'],
            ['\\STRING'],
            ['\\int'],
            ['\\Int'],
            ['\\INT'],
            ['\\float'],
            ['\\Float'],
            ['\\FLOAT'],
            ['\\bool'],
            ['\\Bool'],
            ['\\BOOL'],
            ['\\void'],
            ['\\Void'],
            ['\\VOID'],
            ['?void'],
            ['?Void'],
            ['?VOID'],
            ['\\iterable'],
            ['\\Iterable'],
            ['\\ITERABLE'],
            ['\\object'],
            ['\\Object'],
            ['\\OBJECT'],
        ];

        return array_combine(
            array_map('reset', $invalid),
            $invalid
        );
    }
}
