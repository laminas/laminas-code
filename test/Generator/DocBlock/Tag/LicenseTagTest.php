<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag\LicenseTag;

/**
 * @category   Laminas
 * @package    Laminas_Code_Generator
 * @subpackage UnitTests
 *
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
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

    public function tearDown()
    {
        $this->tag = null;
    }

    public function testUrlGetterAndSetterPersistValue()
    {
        $this->tag->setUrl('foo');
        $this->tag->setLicenseName('bar');

        $this->assertEquals('foo', $this->tag->getUrl());
        $this->assertEquals('bar', $this->tag->getLicenseName());
    }

    public function testLicenseProducesCorrectDocBlockLine()
    {
        $this->tag->setUrl('foo');
        $this->tag->setLicenseName('bar bar bar');
        $this->assertEquals('@license foo bar bar bar', $this->tag->generate());
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions(array(
            'url' => 'foo',
            'licenseName' => 'bar',
        ));
        $tagWithOptionsFromConstructor = new LicenseTag(array(
            'url' => 'foo',
            'licenseName' => 'bar',
        ));
        $this->assertEquals($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }
}
