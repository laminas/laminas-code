<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection\DocBlock\Tag;

use Laminas\Code\Reflection\DocBlock\Tag\ThrowsTag;

/**
 * @group      Laminas_Reflection
 * @group      Laminas_Reflection_DocBlock
 */
class ThrowsTagTest extends \PHPUnit_Framework_TestCase
{
    public function testAllCharactersFromTypenameAreSupported()
    {
        $tag = new ThrowsTag();
        $tag->initialize('\\Logic_2_Exception');
        $this->assertEquals(['\\Logic_2_Exception'], $tag->getTypes());
    }

    public function testSingleTypeWithDescription()
    {
        $tag = new ThrowsTag();
        $tag->initialize('LogicException The Exception');
        $this->assertEquals(['LogicException'], $tag->getTypes());
        $this->assertEquals('The Exception', $tag->getDescription());
    }

    public function testSingleTypeWithoutDescription()
    {
        $tag = new ThrowsTag();
        $tag->initialize('LogicException');
        $this->assertEquals(['LogicException'], $tag->getTypes());
        $this->assertNull($tag->getDescription());
    }

    public function testMultipleTypesWithoutDescription()
    {
        $tag = new ThrowsTag();
        $tag->initialize('LogicException|RuntimeException');
        $this->assertEquals(['LogicException', 'RuntimeException'], $tag->getTypes());
        $this->assertNull($tag->getDescription());
    }

    public function testMultipleTypesWithDescription()
    {
        $tag = new ThrowsTag();
        $tag->initialize('LogicException|RuntimeException The Exception');
        $this->assertEquals(['LogicException', 'RuntimeException'], $tag->getTypes());
        $this->assertEquals('The Exception', $tag->getDescription());
    }
}
