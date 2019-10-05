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
use Zend\Code\Reflection\DocBlock\Tag\LicenseTag;

/**
 * @group      Zend_Reflection
 * @group      Zend_Reflection_DocBlock
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
