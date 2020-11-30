<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection\DocBlock\Tag;

use Laminas\Code\Reflection\DocBlock\Tag\GenericTag;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Reflection
 * @group      Laminas_Reflection_DocBlock
 */
class GenericTagTest extends TestCase
{
    /**
     * @group Laminas-146
     */
    public function testParse()
    {
        $tag = new GenericTag();
        $tag->initialize('baz zab');
        self::assertEquals('baz', $tag->returnValue(0));
        self::assertEquals('zab', $tag->returnValue(1));
    }
}
