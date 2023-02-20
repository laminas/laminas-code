<?php

namespace LaminasTest\Code\Reflection\DocBlock\Tag;

use Laminas\Code\Reflection\DocBlock\Tag\ThrowsTag;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Laminas_Reflection')]
#[Group('Laminas_Reflection_DocBlock')]
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
