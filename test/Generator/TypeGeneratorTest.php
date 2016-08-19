<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Generator;

use Zend\Code\Exception\InvalidArgumentException;
use Zend\Code\Generator\GeneratorInterface;
use Zend\Code\Generator\TypeGenerator;

/**
 * @group zendframework/zend-code#29
 *
 * @requires PHP 7.0
 *
 * @covers \Zend\Code\Generator\TypeGenerator
 */
class TypeGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAGenerator()
    {
        self::assertContains(GeneratorInterface::class, class_implements(TypeGenerator::class));
    }

    /**
     * @dataProvider validTypeProvider
     *
     * @param string $typeString
     * @param string $expectedReturnType
     */
    public function testFromValidTypeString(string $typeString, string $expectedReturnType)
    {
        $generator = TypeGenerator::fromTypeString($typeString);

        self::assertSame($expectedReturnType, $generator->generate());
    }

    /**
     * @dataProvider validTypeProvider
     *
     * @param string $typeString
     * @param string $expectedReturnType
     */
    public function testStringCastFromValidTypeString(string $typeString, string $expectedReturnType)
    {
        $generator = TypeGenerator::fromTypeString($typeString);

        self::assertSame(ltrim($expectedReturnType, '\\'), (string) $generator);
    }

    /**
     * @dataProvider validClassNameProvider
     *
     * @param string $typeString
     * @param string $expectedReturnType
     */
    public function testStripsPrefixingBackslashFromClassNames(string $typeString, string $expectedReturnType)
    {
        $generator = TypeGenerator::fromTypeString('\\' . $typeString);

        self::assertSame($expectedReturnType, $generator->generate());
        self::assertSame(ltrim($expectedReturnType, '\\'), (string) $generator);
    }

    /**
     * @dataProvider invalidTypeProvider
     *
     * @param string $typeString
     */
    public function testRejectsInvalidTypeString(string $typeString)
    {
        $this->setExpectedException(InvalidArgumentException::class);

        TypeGenerator::fromTypeString($typeString);
    }

    /**
     * @return string[][]
     */
    public function validTypeProvider()
    {
        return [
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
            ['object', '\\object'],
            ['Object', '\\Object'],
            ['OBJECT', '\\OBJECT'],
            ['mixed', '\\mixed'],
            ['Mixed', '\\Mixed'],
            ['MIXED', '\\MIXED'],
            ['resource', '\\resource'],
            ['Resource', '\\Resource'],
            ['RESOURCE', '\\RESOURCE'],
            ['foo_bar', '\\foo_bar'],
            ["\x80", "\\\x80"],
            ["\x80\\\x80", "\\\x80\\\x80"],
        ];
    }

    /**
     * Valid class names - just the same as validTypeProvider, but with only those elements prefixed by '\\'
     *
     * @return string[][]
     */
    public function validClassNameProvider()
    {
        return array_filter(
            $this->validTypeProvider(),
            function (array $pair) {
                return 0 === strpos($pair[1], '\\');
            }
        );
    }

    /**
     * @return string[][]
     */
    public function invalidTypeProvider()
    {
        return [
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
        ];
    }
}
