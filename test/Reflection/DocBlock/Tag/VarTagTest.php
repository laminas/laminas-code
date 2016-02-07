<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Reflection\DocBlock\Tag;

use Zend\Code\Reflection\DocBlock\Tag\VarTag;

/**
 * @group      Zend_Reflection
 * @group      Zend_Reflection_DocBlock
 */
class VarTagTest extends \PHPUnit_Framework_TestCase
{
    public function testParseName()
    {
        $tag = new VarTag();
        $tag->initialize('$test');
        $this->assertEquals('var', $tag->getName());
        $this->assertEquals('$test', $tag->getVariableName());
        $this->assertNull($tag->getDescription());
    }

    public function testParseTypeAndName()
    {
        $tag = new VarTag();
        $tag->initialize('string|null $test');
        $this->assertEquals('$test', $tag->getVariableName());
        $this->assertNull($tag->getDescription());
        $this->assertEquals(['string', 'null'], $tag->getTypes());
    }

    public function testParseNameAndDescription()
    {
        $tag = new VarTag();
        $tag->initialize('$test I\'m test property');
        $this->assertEquals('$test', $tag->getVariableName());
        $this->assertEquals('I\'m test property', $tag->getDescription());
    }

    public function testParseTypeAndNameAndDescription()
    {
        $tag = new VarTag();
        $tag->initialize('string $test I\'m test variable');
        $this->assertEquals('$test', $tag->getVariableName());
        $this->assertEquals('I\'m test variable', $tag->getDescription());
    }
}
