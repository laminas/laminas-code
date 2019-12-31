<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag\MethodTag;
use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlockReflection;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class MethodTagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MethodTag
     */
    protected $tag;

    /**
     * @var TagManager
     */
    protected $tagmanager;

    public function setUp()
    {
        $this->tag = new MethodTag();
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
        $this->tag->setIsStatic(true);
        $this->tag->setMethodName('method');
        $this->assertEquals(true, $this->tag->isStatic());
        $this->assertEquals('method', $this->tag->getMethodName());
    }

    public function testGetterForMethodNameTrimsCorrectly()
    {
        $this->tag->setMethodName('()method()');
        $this->assertEquals('()method', $this->tag->getMethodName());
    }

    public function testNameIsCorrect()
    {
        $this->assertEquals('method', $this->tag->getName());
    }

    public function testParamProducesCorrectDocBlockLine()
    {
        $this->tag->setIsStatic(true);
        $this->tag->setMethodName('method');
        $this->tag->setTypes('int');
        $this->tag->setDescription('method(string $a)');
        $this->assertEquals('@method static int method() method(string $a)', $this->tag->generate());
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions([
            'isStatic' => true,
            'methodName' => 'method',
            'types' => ['string'],
            'description' => 'description'
        ]);
        $tagWithOptionsFromConstructor = new MethodTag('method', ['string'], 'description', true);
        $this->assertEquals($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @method static int method() method(int $a)');
        $reflectionTag = $docreflection->getTag('method');

        /** @var MethodTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        $this->assertInstanceOf('Laminas\Code\Generator\DocBlock\Tag\MethodTag', $tag);
        $this->assertEquals(true, $tag->isStatic());
        $this->assertEquals('int', $tag->getTypesAsString());
        $this->assertEquals('method', $tag->getMethodName());
        $this->assertEquals('method(int $a)', $tag->getDescription());
    }
}
