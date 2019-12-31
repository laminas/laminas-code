<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection\DocBlock\Tag;

use Laminas\Code\Reflection\DocBlock\Tag\PropertyTag;

/**
 * @category   Laminas
 * @package    Laminas_Reflection
 * @subpackage UnitTests
 * @group      Laminas_Reflection
 * @group      Laminas_Reflection_DocBlock
 */
class PropertyTagTest extends \PHPUnit_Framework_TestCase
{
    public function testParseName()
    {
        $tag = new PropertyTag();
        $tag->initialize('$test');
        $this->assertEquals('property', $tag->getName());
        $this->assertEquals('$test', $tag->getPropertyName());
        $this->assertNull($tag->getType());
        $this->assertNull($tag->getDescription());
    }

    public function testParseTypeAndName()
    {
        $tag = new PropertyTag();
        $tag->initialize('string $test');
        $this->assertEquals('$test', $tag->getPropertyName());
        $this->assertNull($tag->getDescription());
        $this->assertEquals('string', $tag->getType());
    }

    public function testParseNameAndDescription()
    {
        $tag = new PropertyTag();
        $tag->initialize('$test I\'m test property');
        $this->assertEquals('$test', $tag->getPropertyName());
        $this->assertNull($tag->getType());
        $this->assertEquals('I\'m test property', $tag->getDescription());
    }

    public function testParseTypeAndNameAndDescription()
    {
        $tag = new PropertyTag();
        $tag->initialize('string $test I\'m test property');
        $this->assertEquals('$test', $tag->getPropertyName());
        $this->assertEquals('string', $tag->getType());
        $this->assertEquals('I\'m test property', $tag->getDescription());
    }
}
