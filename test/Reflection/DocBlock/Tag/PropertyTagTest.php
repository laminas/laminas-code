<?php

namespace LaminasTest\Code\Reflection\DocBlock\Tag;

use Laminas\Code\Reflection\DocBlock\Tag\PropertyTag;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Laminas_Reflection')]
#[Group('Laminas_Reflection_DocBlock')]
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
