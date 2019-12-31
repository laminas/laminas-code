<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection\DocBlock\Tag;

use Laminas\Code\Reflection\DocBlock\Tag\VarTag;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Reflection
 * @group      Laminas_Reflection_DocBlock
 */
class VarTagTest extends TestCase
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
