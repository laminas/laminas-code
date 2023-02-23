<?php

namespace LaminasTest\Code\Reflection\DocBlock\Tag;

use Laminas\Code\Reflection\DocBlock\Tag\LicenseTag;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Laminas_Reflection')]
#[Group('Laminas_Reflection_DocBlock')]
class LicenseTagTest extends TestCase
{
    /** @var LicenseTag */
    protected $tag;

    protected function setUp(): void
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
