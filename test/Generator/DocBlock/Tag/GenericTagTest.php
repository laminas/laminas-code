<?php

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag\GenericTag;
use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlockReflection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Laminas_Code_Generator')]
#[Group('Laminas_Code_Generator_Php')]
class GenericTagTest extends TestCase
{
    /** @var GenericTag */
    protected $tag;

    /** @var TagManager */
    protected $tagmanager;

    protected function setUp(): void
    {
        $this->tag        = new GenericTag();
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
        $this->tag->setName('var');
        $this->tag->setContent('string');
        self::assertSame('var', $this->tag->getName());
        self::assertSame('string', $this->tag->getContent());
    }

    public function testParamProducesCorrectDocBlockLine()
    {
        $this->tag->setName('var');
        $this->tag->setContent('string');
        self::assertSame('@var string', $this->tag->generate());
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions([
            'name'    => 'var',
            'content' => 'string',
        ]);
        $tagWithOptionsFromConstructor = new GenericTag('var', 'string');
        self::assertSame($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @global string');
        $reflectionTag = $docreflection->getTag('global');

        /** @var GenericTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        self::assertInstanceOf(GenericTag::class, $tag);
        self::assertSame('global', $tag->getName());
        self::assertSame('string', $tag->getContent());
    }
}
