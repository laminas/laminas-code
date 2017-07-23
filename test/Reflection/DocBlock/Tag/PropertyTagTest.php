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
use Zend\Code\Reflection\DocBlock\Tag\PropertyTag;

/**
 * @group      Zend_Reflection
 * @group      Zend_Reflection_DocBlock
 */
class PropertyTagTest extends TestCase
{
    public function testParseName()
    {
        $tag = new PropertyTag();
        $tag->initialize('$test');
        self::assertEquals('property', $tag->getName());
        self::assertEquals('$test', $tag->getPropertyName());
        self::assertNull($tag->getType());
        self::assertNull($tag->getDescription());
    }

    public function testParseTypeAndName()
    {
        $tag = new PropertyTag();
        $tag->initialize('string|null $test');
        self::assertEquals('$test', $tag->getPropertyName());
        self::assertNull($tag->getDescription());
        self::assertEquals('string', $tag->getType());
        self::assertEquals(['string', 'null'], $tag->getTypes());
    }

    public function testParseNameAndDescription()
    {
        $tag = new PropertyTag();
        $tag->initialize('$test I\'m test property');
        self::assertEquals('$test', $tag->getPropertyName());
        self::assertNull($tag->getType());
        self::assertEquals('I\'m test property', $tag->getDescription());
    }

    public function testParseTypeAndNameAndDescription()
    {
        $tag = new PropertyTag();
        $tag->initialize('string $test I\'m test property');
        self::assertEquals('$test', $tag->getPropertyName());
        self::assertEquals('string', $tag->getType());
        self::assertEquals('I\'m test property', $tag->getDescription());
    }
}
