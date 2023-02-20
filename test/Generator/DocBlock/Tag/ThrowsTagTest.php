<?php

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag\ThrowsTag;
use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlockReflection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Laminas_Code_Generator')]
#[Group('Laminas_Code_Generator_Php')]
class ThrowsTagTest extends TestCase
{
    /** @var ThrowsTag */
    protected $tag;

    /** @var TagManager */
    protected $tagmanager;

    protected function setUp(): void
    {
        $this->tag        = new ThrowsTag();
        $this->tagmanager = new TagManager();
        $this->tagmanager->initializeDefaultTags();
    }

    protected function tearDown(): void
    {
        $this->tag        = null;
        $this->tagmanager = null;
    }

    public function testNameIsCorrect()
    {
        self::assertSame('throws', $this->tag->getName());
    }

    public function testParamProducesCorrectDocBlockLine()
    {
        $this->tag->setTypes('Exception\\MyException');
        $this->tag->setDescription('description');
        self::assertSame('@throws Exception\\MyException description', $this->tag->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @throws Exception\Invalid description');
        $reflectionTag = $docreflection->getTag('throws');

        /** @var ThrowsTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        self::assertInstanceOf(ThrowsTag::class, $tag);
        self::assertSame('description', $tag->getDescription());
        self::assertSame('Exception\Invalid', $tag->getTypesAsString());
    }
}
