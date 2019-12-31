<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag\ThrowsTag;
use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlockReflection;
use PHPUnit\Framework\TestCase;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class ThrowsTagTest extends TestCase
{
    /**
     * @var ThrowsTag
     */
    protected $tag;

    /**
     * @var TagManager
     */
    protected $tagmanager;

    protected function setUp() : void
    {
        $this->tag = new ThrowsTag();
        $this->tagmanager = new TagManager();
        $this->tagmanager->initializeDefaultTags();
    }

    protected function tearDown() : void
    {
        $this->tag = null;
        $this->tagmanager = null;
    }

    public function testNameIsCorrect()
    {
        self::assertEquals('throws', $this->tag->getName());
    }

    public function testParamProducesCorrectDocBlockLine()
    {
        $this->tag->setTypes('Exception\\MyException');
        $this->tag->setDescription('description');
        self::assertEquals('@throws Exception\\MyException description', $this->tag->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @throws Exception\Invalid description');
        $reflectionTag = $docreflection->getTag('throws');

        /** @var ThrowsTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        self::assertInstanceOf(ThrowsTag::class, $tag);
        self::assertEquals('description', $tag->getDescription());
        self::assertEquals('Exception\Invalid', $tag->getTypesAsString());
    }
}
