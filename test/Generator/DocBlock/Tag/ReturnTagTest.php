<?php

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag\ReturnTag;
use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlockReflection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Laminas_Code_Generator')]
#[Group('Laminas_Code_Generator_Php')]
class ReturnTagTest extends TestCase
{
    /** @var ReturnTag */
    protected $tag;

    /** @var TagManager */
    protected $tagmanager;

    protected function setUp(): void
    {
        $this->tag        = new ReturnTag();
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
        self::assertSame('return', $this->tag->getName());
    }

    public function testReturnProducesCorrectDocBlockLine()
    {
        $this->tag->setTypes('string|int');
        $this->tag->setDescription('bar bar bar');
        self::assertSame('@return string|int bar bar bar', $this->tag->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @return int The return');
        $reflectionTag = $docreflection->getTag('return');

        /** @var ReturnTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        self::assertInstanceOf(ReturnTag::class, $tag);
        self::assertSame('The return', $tag->getDescription());
        self::assertSame('int', $tag->getTypesAsString());
    }
}
