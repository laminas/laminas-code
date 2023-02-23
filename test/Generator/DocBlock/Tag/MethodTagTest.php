<?php

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag\MethodTag;
use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlockReflection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Laminas_Code_Generator')]
#[Group('Laminas_Code_Generator_Php')]
class MethodTagTest extends TestCase
{
    /** @var MethodTag */
    protected $tag;

    /** @var TagManager */
    protected $tagmanager;

    protected function setUp(): void
    {
        $this->tag        = new MethodTag();
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
        $this->tag->setIsStatic(true);
        $this->tag->setMethodName('method');
        self::assertSame(true, $this->tag->isStatic());
        self::assertSame('method', $this->tag->getMethodName());
    }

    public function testGetterForMethodNameTrimsCorrectly()
    {
        $this->tag->setMethodName('()method()');
        self::assertSame('()method', $this->tag->getMethodName());
    }

    public function testNameIsCorrect()
    {
        self::assertSame('method', $this->tag->getName());
    }

    public function testParamProducesCorrectDocBlockLine()
    {
        $this->tag->setIsStatic(true);
        $this->tag->setMethodName('method');
        $this->tag->setTypes('int');
        $this->tag->setDescription('method(string $a)');
        self::assertSame('@method static int method() method(string $a)', $this->tag->generate());
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions([
            'isStatic'    => true,
            'methodName'  => 'method',
            'types'       => ['string'],
            'description' => 'description',
        ]);
        $tagWithOptionsFromConstructor = new MethodTag('method', ['string'], 'description', true);
        self::assertSame($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @method static int method() method(int $a)');
        $reflectionTag = $docreflection->getTag('method');

        /** @var MethodTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        self::assertInstanceOf(MethodTag::class, $tag);
        self::assertSame(true, $tag->isStatic());
        self::assertSame('int', $tag->getTypesAsString());
        self::assertSame('method', $tag->getMethodName());
        self::assertSame('method(int $a)', $tag->getDescription());
    }
}
