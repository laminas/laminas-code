<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag\LicenseTag;
use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlockReflection;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class LicenseTagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LicenseTag
     */
    protected $tag;
    /**
     * @var TagManager
     */
    protected $tagmanager;

    public function setUp()
    {
        $this->tag = new LicenseTag();
        $this->tagmanager = new TagManager();
        $this->tagmanager->initializeDefaultTags();
    }

    public function tearDown()
    {
        $this->tag = null;
        $this->tagmanager = null;
    }

    public function testGetterAndSetterPersistValue()
    {
        $this->tag->setUrl('foo');
        $this->tag->setLicenseName('bar');

        $this->assertEquals('foo', $this->tag->getUrl());
        $this->assertEquals('bar', $this->tag->getLicenseName());
    }

    public function testNameIsCorrect()
    {
        $this->assertEquals('license', $this->tag->getName());
    }

    public function testLicenseProducesCorrectDocBlockLine()
    {
        $this->tag->setUrl('foo');
        $this->tag->setLicenseName('bar bar bar');
        $this->assertEquals('@license foo bar bar bar', $this->tag->generate());
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions([
            'url' => 'foo',
            'licenseName' => 'bar',
        ]);
        $tagWithOptionsFromConstructor = new LicenseTag('foo', 'bar');
        $this->assertEquals($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @license http://zend.com License');
        $reflectionTag = $docreflection->getTag('license');

        /** @var LicenseTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        $this->assertInstanceOf('Laminas\Code\Generator\DocBlock\Tag\LicenseTag', $tag);
        $this->assertEquals('http://zend.com', $tag->getUrl());
        $this->assertEquals('License', $tag->getLicenseName());
    }
}
