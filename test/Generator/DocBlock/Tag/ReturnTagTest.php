<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag\ReturnTag;
use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlockReflection;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class ReturnTagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReturnTag
     */
    protected $tag;
    /**
     * @var TagManager
     */
    protected $tagmanager;

    public function setUp()
    {
        $this->tag = new ReturnTag();
        $this->tagmanager = new TagManager();
        $this->tagmanager->initializeDefaultTags();
    }

    public function tearDown()
    {
        $this->tag = null;
        $this->tagmanager = null;
    }

    public function testNameIsCorrect()
    {
        $this->assertEquals('return', $this->tag->getName());
    }

    public function testReturnProducesCorrectDocBlockLine()
    {
        $this->tag->setTypes('string|int');
        $this->tag->setDescription('bar bar bar');
        $this->assertEquals('@return string|int bar bar bar', $this->tag->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @return int The return');
        $reflectionTag = $docreflection->getTag('return');

        /** @var ReturnTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        $this->assertInstanceOf('Laminas\Code\Generator\DocBlock\Tag\ReturnTag', $tag);
        $this->assertEquals('The return', $tag->getDescription());
        $this->assertEquals('int', $tag->getTypesAsString());
    }
}
