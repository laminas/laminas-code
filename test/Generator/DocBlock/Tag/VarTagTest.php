<?php

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag\VarTag;
use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlock\Tag\VarTag as ReflectionVarTag;
use Laminas\Code\Reflection\DocBlockReflection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VarTag::class)]
class VarTagTest extends TestCase
{
    private VarTag $tag;

    private TagManager $tagManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tag        = new VarTag();
        $this->tagManager = new TagManager();

        $this->tagManager->initializeDefaultTags();
    }

    public function testGetterAndSetterPersistValue(): void
    {
        $tag = new VarTag('variable');

        self::assertSame('variable', $tag->getVariableName());
    }

    public function testGetterForVariableNameTrimsCorrectly(): void
    {
        $this->tag->setVariableName('$variable$');
        $this->assertSame('variable$', $this->tag->getVariableName());
    }

    public function testNameIsCorrect(): void
    {
        $this->assertSame('var', $this->tag->getName());
    }

    public function testParamProducesCorrectDocBlockLine(): void
    {
        $this->tag->setVariableName('variable');
        $this->tag->setTypes('string[]');
        $this->tag->setDescription('description');
        $this->assertSame('@var string[] $variable description', $this->tag->generate());
    }

    public function testConstructorWithOptions(): void
    {
        $this->tag->setOptions([
            'variableName' => 'foo',
            'types'        => ['string'],
            'description'  => 'description',
        ]);
        $tagWithOptionsFromConstructor = new VarTag('foo', ['string'], 'description');
        $this->assertSame($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }

    public function testCreatingTagFromReflection(): void
    {
        $reflectionTag = (new DocBlockReflection('/** @var int $foo description'))
            ->getTag('var');

        self::assertInstanceOf(ReflectionVarTag::class, $reflectionTag);

        /** @var VarTag $tag */
        $tag = $this->tagManager->createTagFromReflection($reflectionTag);

        $this->assertInstanceOf(VarTag::class, $tag);
        $this->assertSame('foo', $tag->getVariableName());
        $this->assertSame('description', $tag->getDescription());
        $this->assertSame('int', $tag->getTypesAsString());
    }
}
