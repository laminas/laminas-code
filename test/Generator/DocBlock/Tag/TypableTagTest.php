<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use LaminasTest\Code\Generator\TestAsset\TypeableTag;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class TypableTagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TypeableTag
     */
    protected $tag;

    public function setUp()
    {
        $this->tag = new TypeableTag();
    }

    public function tearDown()
    {
        $this->tag = null;
    }

    public function testGetterAndSetterPersistValue()
    {
        $this->tag->setTypes(['string', 'null']);
        $this->tag->setDescription('Description');
        $this->assertEquals(['string', 'null'], $this->tag->getTypes());
        $this->assertEquals('Description', $this->tag->getDescription());
    }

    public function testGetterForTypesAsStringWithSingleType()
    {
        $this->tag->setTypes(['string']);
        $this->assertEquals('string', $this->tag->getTypesAsString());
    }

    public function testGetterForTypesAsStringWithSingleTypeAndDelimiter()
    {
        $this->tag->setTypes(['string']);
        $this->assertEquals('string', $this->tag->getTypesAsString('/'));
    }

    public function testGetterForTypesAsStringWithMultipleTypes()
    {
        $this->tag->setTypes(['string', 'null']);
        $this->assertEquals('string|null', $this->tag->getTypesAsString());
    }

    public function testGetterForTypesAsStringWithMultipleTypesAndDelimiter()
    {
        $this->tag->setTypes(['string', 'null']);
        $this->assertEquals('string/null', $this->tag->getTypesAsString('/'));
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions([
            'types' => ['string', 'null'],
            'description' => 'description',
        ]);
        $tagWithOptionsFromConstructor = new TypeableTag(['string', 'null'], 'description');
        $this->assertEquals($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }
}
