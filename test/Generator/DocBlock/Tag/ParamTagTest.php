<?php

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag\ParamTag;
use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlockReflection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Laminas_Code_Generator')]
#[Group('Laminas_Code_Generator_Php')]
class ParamTagTest extends TestCase
{
    /** @var ParamTag */
    protected $tag;

    /** @var TagManager */
    protected $tagmanager;

    protected function setUp(): void
    {
        $this->tag        = new ParamTag();
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
        $this->tag->setVariableName('Foo');
        self::assertSame('Foo', $this->tag->getVariableName());
    }

    public function testGetterForVariableNameTrimsCorrectly()
    {
        $this->tag->setVariableName('$param$');
        self::assertSame('param$', $this->tag->getVariableName());
    }

    public function testNameIsCorrect()
    {
        self::assertSame('param', $this->tag->getName());
    }

    public function testParamProducesCorrectDocBlockLine()
    {
        $this->tag->setVariableName('foo');
        $this->tag->setTypes('string|null');
        $this->tag->setDescription('description');
        self::assertSame('@param string|null $foo description', $this->tag->generate());
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions([
            'variableName' => 'foo',
            'types'        => ['string'],
            'description'  => 'description',
        ]);
        $tagWithOptionsFromConstructor = new ParamTag('foo', ['string'], 'description');
        self::assertSame($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @param int $foo description');
        $reflectionTag = $docreflection->getTag('param');

        /** @var ParamTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        self::assertInstanceOf(ParamTag::class, $tag);
        self::assertSame('foo', $tag->getVariableName());
        self::assertSame('description', $tag->getDescription());
        self::assertSame('int', $tag->getTypesAsString());
    }
}
