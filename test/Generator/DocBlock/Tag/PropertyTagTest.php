<?php

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag\PropertyTag;
use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlockReflection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Laminas_Code_Generator')]
#[Group('Laminas_Code_Generator_Php')]
class PropertyTagTest extends TestCase
{
    /** @var PropertyTag */
    protected $tag;

    /** @var TagManager */
    protected $tagmanager;

    protected function setUp(): void
    {
        $this->tag        = new PropertyTag();
        $this->tagmanager = new TagManager();
        $this->tagmanager->initializeDefaultTags();
    }

    protected function tearDown(): void
    {
        $this->tag        = null;
        $this->tagmanager = null;
    }

    public function testGetterAndSetterPersistValue()
    {
        $this->tag->setPropertyName('property');
        self::assertSame('property', $this->tag->getPropertyName());
    }

    public function testGetterForVariableNameTrimsCorrectly()
    {
        $this->tag->setPropertyName('$property$');
        self::assertSame('property$', $this->tag->getPropertyName());
    }

    public function testNameIsCorrect()
    {
        self::assertSame('property', $this->tag->getName());
    }

    public function testParamProducesCorrectDocBlockLine()
    {
        $this->tag->setPropertyName('property');
        $this->tag->setTypes('string[]');
        $this->tag->setDescription('description');
        self::assertSame('@property string[] $property description', $this->tag->generate());
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions([
            'propertyName' => 'property',
            'types'        => ['string'],
            'description'  => 'description',
        ]);
        $tagWithOptionsFromConstructor = new PropertyTag('property', ['string'], 'description');
        self::assertSame($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @property int $foo description');
        $reflectionTag = $docreflection->getTag('property');

        /** @var PropertyTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        self::assertInstanceOf(PropertyTag::class, $tag);
        self::assertSame('foo', $tag->getPropertyName());
        self::assertSame('description', $tag->getDescription());
        self::assertSame('int', $tag->getTypesAsString());
    }
}
