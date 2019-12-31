<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection\DocBlock\Tag;

use Laminas\Code\Reflection\DocBlock\Tag\LicenseTag;

/**
 * @category   Laminas
 * @package    Laminas_Reflection
 * @subpackage UnitTests
 * @group      Laminas_Reflection
 * @group      Laminas_Reflection_DocBlock
 */
class LicenseTagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LicenseTag
     */
    protected $tag;

    public function setUp()
    {
        $this->tag = new LicenseTag();
    }

    public function testParseUrl()
    {
        $this->tag->initialize('http://www.example.com');
        $this->assertEquals('license', $this->tag->getName());
        $this->assertEquals('http://www.example.com', $this->tag->getUrl());
    }

    public function testParseUrlAndLicenseName()
    {
        $this->tag->initialize('http://www.example.com Foo');
        $this->assertEquals('license', $this->tag->getName());
        $this->assertEquals('http://www.example.com', $this->tag->getUrl());
        $this->assertEquals('Foo', $this->tag->getLicenseName());
    }
}
