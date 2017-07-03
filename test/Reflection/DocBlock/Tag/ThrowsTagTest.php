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
use Zend\Code\Reflection\DocBlock\Tag\ThrowsTag;

/**
 * @group      Zend_Reflection
 * @group      Zend_Reflection_DocBlock
 */
class ThrowsTagTest extends TestCase
{
    public function testAllCharactersFromTypenameAreSupported()
    {
        $tag = new ThrowsTag();
        $tag->initialize('\\Logic_2_Exception');
        self::assertEquals(['\\Logic_2_Exception'], $tag->getTypes());
    }

    public function testSingleTypeWithDescription()
    {
        $tag = new ThrowsTag();
        $tag->initialize('LogicException The Exception');
        self::assertEquals(['LogicException'], $tag->getTypes());
        self::assertEquals('The Exception', $tag->getDescription());
    }

    public function testSingleTypeWithoutDescription()
    {
        $tag = new ThrowsTag();
        $tag->initialize('LogicException');
        self::assertEquals(['LogicException'], $tag->getTypes());
        self::assertNull($tag->getDescription());
    }

    public function testMultipleTypesWithoutDescription()
    {
        $tag = new ThrowsTag();
        $tag->initialize('LogicException|RuntimeException');
        self::assertEquals(['LogicException', 'RuntimeException'], $tag->getTypes());
        self::assertNull($tag->getDescription());
    }

    public function testMultipleTypesWithDescription()
    {
        $tag = new ThrowsTag();
        $tag->initialize('LogicException|RuntimeException The Exception');
        self::assertEquals(['LogicException', 'RuntimeException'], $tag->getTypes());
        self::assertEquals('The Exception', $tag->getDescription());
    }
}
