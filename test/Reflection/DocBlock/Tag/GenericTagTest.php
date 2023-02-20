<?php

namespace LaminasTest\Code\Reflection\DocBlock\Tag;

use Laminas\Code\Reflection\DocBlock\Tag\GenericTag;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Laminas_Reflection')]
#[Group('Laminas_Reflection_DocBlock')]
class GenericTagTest extends TestCase
{
    #[Group('Laminas-146')]
    public function testParse()
    {
        $tag = new GenericTag();
        $tag->initialize('baz zab');
        self::assertEquals('baz', $tag->returnValue(0));
        self::assertEquals('zab', $tag->returnValue(1));
    }
}
