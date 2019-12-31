<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag\GenericTag;
use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlockReflection;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class GenericTagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GenericTag
     */
    protected $tag;
    /**
     * @var TagManager
     */
    protected $tagmanager;

    public function setUp()
    {
        $this->tag = new GenericTag();
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
        $this->tag->setName('var');
        $this->tag->setContent('string');
        $this->assertEquals('var', $this->tag->getName());
        $this->assertEquals('string', $this->tag->getContent());
    }

    public function testParamProducesCorrectDocBlockLine()
    {
        $this->tag->setName('var');
        $this->tag->setContent('string');
        $this->assertEquals('@var string', $this->tag->generate());
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions([
            'name' => 'var',
            'content' => 'string',
        ]);
        $tagWithOptionsFromConstructor = new GenericTag('var', 'string');
        $this->assertEquals($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @var string');
        $reflectionTag = $docreflection->getTag('var');

        /** @var GenericTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        $this->assertInstanceOf('Laminas\Code\Generator\DocBlock\Tag\GenericTag', $tag);
        $this->assertEquals('var', $tag->getName());
        $this->assertEquals('string', $tag->getContent());
    }
}
