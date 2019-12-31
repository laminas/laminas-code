<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag\AuthorTag;
use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlockReflection;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class AuthorTagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AuthorTag
     */
    protected $tag;

    /**
     * @var TagManager
     */
    protected $tagmanager;

    public function setUp()
    {
        $this->tag = new AuthorTag();
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
        $this->tag->setAuthorName('Foo');
        $this->tag->setAuthorEmail('Bar');
        $this->assertEquals('Foo', $this->tag->getAuthorName());
        $this->assertEquals('Bar', $this->tag->getAuthorEmail());
    }

    public function testParamProducesCorrectDocBlockLine()
    {
        $this->tag->setAuthorName('foo');
        $this->tag->setAuthorEmail('string');
        $this->assertEquals('@author foo <string>', $this->tag->generate());
    }

    public function testNameIsCorrect()
    {
        $this->assertEquals('author', $this->tag->getName());
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions([
            'authorEmail' => 'string',
            'authorName' => 'foo',
        ]);
        $tagWithOptionsFromConstructor = new AuthorTag('foo', 'string');
        $this->assertEquals($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @author Mister Miller <mister.miller@zend.com>');
        $reflectionTag = $docreflection->getTag('author');

        /** @var AuthorTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        $this->assertInstanceOf('Laminas\Code\Generator\DocBlock\Tag\AuthorTag', $tag);
        $this->assertEquals('Mister Miller', $tag->getAuthorName());
        $this->assertEquals('mister.miller@zend.com', $tag->getAuthorEmail());
    }
}
