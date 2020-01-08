<?php

/**
 * @see       https://github.com/laminasframwork/laminas-code for the canonical source repository
 * @copyright https://github.com/laminasframwork/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminasframwork/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection\DocBlock\Tag;

use Laminas\Code\Reflection\DocBlock\Tag\LicenseTag;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Reflection
 * @group      Laminas_Reflection_DocBlock
 */
class LicenseTagTest extends TestCase
{
    /**
     * @var LicenseTag
     */
    protected $tag;

    protected function setUp() : void
    {
        $this->tag = new LicenseTag();
    }

    public function testParseUrl()
    {
        $this->tag->initialize('http://www.example.com');
        self::assertEquals('license', $this->tag->getName());
        self::assertEquals('http://www.example.com', $this->tag->getUrl());
    }

    public function testParseUrlAndLicenseName()
    {
        $this->tag->initialize('http://www.example.com Foo');
        self::assertEquals('license', $this->tag->getName());
        self::assertEquals('http://www.example.com', $this->tag->getUrl());
        self::assertEquals('Foo', $this->tag->getLicenseName());
    }
}
