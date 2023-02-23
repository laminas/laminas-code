<?php

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use LaminasTest\Code\Generator\TestAsset\TypeableTag;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Laminas_Code_Generator')]
#[Group('Laminas_Code_Generator_Php')]
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
        self::assertSame(['string', 'null'], $this->tag->getTypes());
        self::assertSame('Description', $this->tag->getDescription());
    }

    public function testGetterForTypesAsStringWithSingleType()
    {
        $this->tag->setTypes(['string']);
        self::assertSame('string', $this->tag->getTypesAsString());
    }

    public function testGetterForTypesAsStringWithSingleTypeAndDelimiter()
    {
        $this->tag->setTypes(['string']);
        self::assertSame('string', $this->tag->getTypesAsString('/'));
    }

    public function testGetterForTypesAsStringWithMultipleTypes()
    {
        $this->tag->setTypes(['string', 'null']);
        self::assertSame('string|null', $this->tag->getTypesAsString());
    }

    public function testGetterForTypesAsStringWithMultipleTypesAndDelimiter()
    {
        $this->tag->setTypes(['string', 'null']);
        self::assertSame('string/null', $this->tag->getTypesAsString('/'));
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions([
            'types'       => ['string', 'null'],
            'description' => 'description',
        ]);
        $tagWithOptionsFromConstructor = new TypeableTag(['string', 'null'], 'description');
        self::assertSame($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }
}
