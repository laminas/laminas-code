<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection\DocBlock\Tag;

use Laminas\Code\Reflection\DocBlock\Tag\MethodTag;

/**
 * @group      Laminas_Reflection
 * @group      Laminas_Reflection_DocBlock
 */
class MethodTagTest extends \PHPUnit_Framework_TestCase
{
    public function testParseName()
    {
        $tag = new MethodTag();
        $tag->initialize('test()');
        $this->assertEquals('method', $tag->getName());
        $this->assertEquals('test()', $tag->getMethodName());
        $this->assertFalse($tag->isStatic());
        $this->assertNull($tag->getReturnType());
        $this->assertNull($tag->getDescription());
    }

    public function testParseNameAndType()
    {
        $tag = new MethodTag();
        $tag->initialize('string|null test()');
        $this->assertEquals('method', $tag->getName());
        $this->assertEquals('test()', $tag->getMethodName());
        $this->assertFalse($tag->isStatic());
        $this->assertEquals('string', $tag->getReturnType());
        $this->assertEquals(array('string', 'null'), $tag->getTypes());
        $this->assertNull($tag->getDescription());
    }

    public function testParseNameAndStatic()
    {
        $tag = new MethodTag();
        $tag->initialize('static test()');
        $this->assertEquals('method', $tag->getName());
        $this->assertEquals('test()', $tag->getMethodName());
        $this->assertTrue($tag->isStatic());
        $this->assertNull($tag->getReturnType());
        $this->assertNull($tag->getDescription());
    }

    public function testParseNameAndStaticAndDescription()
    {
        $tag = new MethodTag();
        $tag->initialize('static test() I\'m test method');
        $this->assertEquals('method', $tag->getName());
        $this->assertEquals('test()', $tag->getMethodName());
        $this->assertTrue($tag->isStatic());
        $this->assertNull($tag->getReturnType());
        $this->assertEquals('I\'m test method', $tag->getDescription());
    }

    public function testParseNameAndTypeAndStaticAndDescription()
    {
        $tag = new MethodTag();
        $tag->initialize('static string test() I\'m test method');
        $this->assertEquals('method', $tag->getName());
        $this->assertEquals('test()', $tag->getMethodName());
        $this->assertTrue($tag->isStatic());
        $this->assertEquals('string', $tag->getReturnType());
        $this->assertEquals('I\'m test method', $tag->getDescription());
    }
}
