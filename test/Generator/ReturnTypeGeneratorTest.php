<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Generator;

use Zend\Code\Generator\GeneratorInterface;
use Zend\Code\Generator\ReturnTypeGenerator;

/**
 * @covers \Zend\Code\Generator\ReturnTypeGenerator
 */
class ReturnTypeGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAGenerator()
    {
        self::assertContains(GeneratorInterface::class, class_implements(ReturnTypeGenerator::class));
    }

    /**
     * @dataProvider validReturnTypeProvider
     *
     * @param string $typeString
     * @param string $expectedReturnType
     */
    public function testFromValidReturnTypeString(string $typeString, string $expectedReturnType)
    {
        $generator = ReturnTypeGenerator::fromReturnTypeString($typeString);

        self::assertSame($expectedReturnType, $generator->generate());
    }

    public function validReturnTypeProvider() : array
    {
        return [
            ['foo', '\\foo'],
            ['foo', '\\foo'],
            ['foo1', '\\foo1'],
            ['foo\\bar', '\\\foo\\bar'],
            ['foo\\bar\\baz', '\\foo\\bar\\baz'],
            ['foo\\bar\\baz1', '\\foo\\bar\\baz1'],
            ['FOO', '\\FOO'],
            ['FOO1', '\\FOO1'],
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
            ['OBJECT', '\\Object'],
            ['mixed', '\\mixed'],
            ['Mixed', '\\Mixed'],
            ['MIXED', '\\MIXED'],
            ['resource', '\\resource'],
            ['Resource', '\\Resource'],
            ['RESOURCE', '\\RESOURCE'],
        ];
    }
}
