<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use LaminasTest\Code\Generator\TestAsset\TypeableTag;
use PHPUnit\Framework\TestCase;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class TypableTagTest extends TestCase
{
    /** @var TypeableTag */
    protected $tag;

    protected function setUp(): void
    {
        $this->tag = new TypeableTag();
    }

    protected function tearDown(): void
    {
        $this->tag = null;
    }

    public function testGetterAndSetterPersistValue()
    {
        $this->tag->setTypes(['string', 'null']);
        $this->tag->setDescription('Description');
        self::assertEquals(['string', 'null'], $this->tag->getTypes());
        self::assertEquals('Description', $this->tag->getDescription());
    }

    public function testGetterForTypesAsStringWithSingleType()
    {
        $this->tag->setTypes(['string']);
        self::assertEquals('string', $this->tag->getTypesAsString());
    }

    public function testGetterForTypesAsStringWithSingleTypeAndDelimiter()
    {
        $this->tag->setTypes(['string']);
        self::assertEquals('string', $this->tag->getTypesAsString('/'));
    }

    public function testGetterForTypesAsStringWithMultipleTypes()
    {
        $this->tag->setTypes(['string', 'null']);
        self::assertEquals('string|null', $this->tag->getTypesAsString());
    }

    public function testGetterForTypesAsStringWithMultipleTypesAndDelimiter()
    {
        $this->tag->setTypes(['string', 'null']);
        self::assertEquals('string/null', $this->tag->getTypesAsString('/'));
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions([
            'types'       => ['string', 'null'],
            'description' => 'description',
        ]);
        $tagWithOptionsFromConstructor = new TypeableTag(['string', 'null'], 'description');
        self::assertEquals($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }
}
