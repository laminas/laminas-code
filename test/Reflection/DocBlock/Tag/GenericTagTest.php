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
use Zend\Code\Reflection\DocBlock\Tag\GenericTag;

/**
 * @group      Zend_Reflection
 * @group      Zend_Reflection_DocBlock
 */
class GenericTagTest extends TestCase
{
    /**
     * @group ZF2-146
     */
    public function testParse()
    {
        $tag = new GenericTag();
        $tag->initialize('baz zab');
        self::assertEquals('baz', $tag->returnValue(0));
        self::assertEquals('zab', $tag->returnValue(1));
    }
}
