<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag\PropertyTag;
use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlockReflection;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class PropertyTagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PropertyTag
     */
    protected $tag;
    /**
     * @var TagManager
     */
    protected $tagmanager;

    public function setUp()
    {
        $this->tag = new PropertyTag();
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
        $this->tag->setPropertyName('property');
        $this->assertEquals('property', $this->tag->getPropertyName());
    }


    public function testGetterForVariableNameTrimsCorrectly()
    {
        $this->tag->setPropertyName('$property$');
        $this->assertEquals('property$', $this->tag->getPropertyName());
    }

    public function testNameIsCorrect()
    {
        $this->assertEquals('property', $this->tag->getName());
    }

    public function testParamProducesCorrectDocBlockLine()
    {
        $this->tag->setPropertyName('property');
        $this->tag->setTypes('string[]');
        $this->tag->setDescription('description');
        $this->assertEquals('@property string[] $property description', $this->tag->generate());
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions(array(
            'propertyName' => 'property',
            'types' => array('string'),
            'description' => 'description'
        ));
        $tagWithOptionsFromConstructor = new PropertyTag('property', array('string'), 'description');
        $this->assertEquals($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @property int $foo description');
        $reflectionTag = $docreflection->getTag('property');

        /** @var PropertyTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        $this->assertInstanceOf('Laminas\Code\Generator\DocBlock\Tag\PropertyTag', $tag);
        $this->assertEquals('foo', $tag->getPropertyName());
        $this->assertEquals('description', $tag->getDescription());
        $this->assertEquals('int', $tag->getTypesAsString());
    }
}
