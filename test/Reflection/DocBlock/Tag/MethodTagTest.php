<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Reflection\DocBlock\Tag;

use PHPUnit\Framework\TestCase;
use Zend\Code\Reflection\DocBlock\Tag\MethodTag;

/**
 * @group      Zend_Reflection
 * @group      Zend_Reflection_DocBlock
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
