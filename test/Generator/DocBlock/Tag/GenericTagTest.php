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
use PHPUnit\Framework\TestCase;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class GenericTagTest extends TestCase
{
    /**
     * @var GenericTag
     */
    protected $tag;

    /**
     * @var TagManager
     */
    protected $tagmanager;

    protected function setUp() : void
    {
        $this->tag = new GenericTag();
        $this->tagmanager = new TagManager();
        $this->tagmanager->initializeDefaultTags();
    }

    protected function tearDown() : void
    {
        $this->tag = null;
        $this->tagmanager = null;
    }

    public function testGetterAndSetterPersistValue()
    {
        $this->tag->setName('var');
        $this->tag->setContent('string');
        self::assertEquals('var', $this->tag->getName());
        self::assertEquals('string', $this->tag->getContent());
    }

    public function testParamProducesCorrectDocBlockLine()
    {
        $this->tag->setName('var');
        $this->tag->setContent('string');
        self::assertEquals('@var string', $this->tag->generate());
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions([
            'name' => 'var',
            'content' => 'string',
        ]);
        $tagWithOptionsFromConstructor = new GenericTag('var', 'string');
        self::assertEquals($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @global string');
        $reflectionTag = $docreflection->getTag('global');

        /** @var GenericTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        self::assertInstanceOf(GenericTag::class, $tag);
        self::assertEquals('global', $tag->getName());
        self::assertEquals('string', $tag->getContent());
    }
}
