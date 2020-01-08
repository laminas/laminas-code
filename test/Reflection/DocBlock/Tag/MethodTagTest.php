<?php

/**
 * @see       https://github.com/laminasframwork/laminas-code for the canonical source repository
 * @copyright https://github.com/laminasframwork/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminasframwork/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection\DocBlock\Tag;

use Laminas\Code\Reflection\DocBlock\Tag\MethodTag;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Reflection
 * @group      Laminas_Reflection_DocBlock
 */
class MethodTagTest extends TestCase
{
    public function testParseName()
    {
        $tag = new MethodTag();
        $tag->initialize('test()');
        self::assertEquals('method', $tag->getName());
        self::assertEquals('test()', $tag->getMethodName());
        self::assertFalse($tag->isStatic());
        self::assertNull($tag->getReturnType());
        self::assertNull($tag->getDescription());
    }

    public function testParseNameAndType()
    {
        $tag = new MethodTag();
        $tag->initialize('string|null test()');
        self::assertEquals('method', $tag->getName());
        self::assertEquals('test()', $tag->getMethodName());
        self::assertFalse($tag->isStatic());
        self::assertEquals('string', $tag->getReturnType());
        self::assertEquals(['string', 'null'], $tag->getTypes());
        self::assertNull($tag->getDescription());
    }

    public function testParseNameAndStatic()
    {
        $tag = new MethodTag();
        $tag->initialize('static test()');
        self::assertEquals('method', $tag->getName());
        self::assertEquals('test()', $tag->getMethodName());
        self::assertTrue($tag->isStatic());
        self::assertNull($tag->getReturnType());
        self::assertNull($tag->getDescription());
    }

    public function testParseNameAndStaticAndDescription()
    {
        $tag = new MethodTag();
        $tag->initialize('static test() I\'m test method');
        self::assertEquals('method', $tag->getName());
        self::assertEquals('test()', $tag->getMethodName());
        self::assertTrue($tag->isStatic());
        self::assertNull($tag->getReturnType());
        self::assertEquals('I\'m test method', $tag->getDescription());
    }

    public function testParseNameAndTypeAndStaticAndDescription()
    {
        $tag = new MethodTag();
        $tag->initialize('static string test() I\'m test method');
        self::assertEquals('method', $tag->getName());
        self::assertEquals('test()', $tag->getMethodName());
        self::assertTrue($tag->isStatic());
        self::assertEquals('string', $tag->getReturnType());
        self::assertEquals('I\'m test method', $tag->getDescription());
    }
}
