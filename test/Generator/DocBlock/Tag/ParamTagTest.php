<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag\ParamTag;
use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlockReflection;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class ParamTagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ParamTag
     */
    protected $tag;
    /**
     * @var TagManager
     */
    protected $tagmanager;

    public function setUp()
    {
        $this->tag = new ParamTag();
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
        $this->tag->setVariableName('Foo');
        $this->assertEquals('Foo', $this->tag->getVariableName());
    }

    public function testGetterForVariableNameTrimsCorrectly()
    {
        $this->tag->setVariableName('$param$');
        $this->assertEquals('param$', $this->tag->getVariableName());
    }

    public function testNameIsCorrect()
    {
        $this->assertEquals('param', $this->tag->getName());
    }

    public function testParamProducesCorrectDocBlockLine()
    {
        $this->tag->setVariableName('foo');
        $this->tag->setTypes('string|null');
        $this->tag->setDescription('description');
        $this->assertEquals('@param string|null $foo description', $this->tag->generate());
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions(array(
            'variableName' => 'foo',
            'types' => array('string'),
            'description' => 'description'
        ));
        $tagWithOptionsFromConstructor = new ParamTag('foo', array('string'), 'description');
        $this->assertEquals($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @param int $foo description');
        $reflectionTag = $docreflection->getTag('param');

        /** @var ParamTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        $this->assertInstanceOf('Laminas\Code\Generator\DocBlock\Tag\ParamTag', $tag);
        $this->assertEquals('foo', $tag->getVariableName());
        $this->assertEquals('description', $tag->getDescription());
        $this->assertEquals('int', $tag->getTypesAsString());
    }
}
