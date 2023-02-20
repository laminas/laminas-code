<?php

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag\AuthorTag;
use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlockReflection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Laminas_Code_Generator')]
#[Group('Laminas_Code_Generator_Php')]
class AuthorTagTest extends TestCase
{
    /** @var AuthorTag */
    protected $tag;

    /** @var TagManager */
    protected $tagmanager;

    protected function setUp(): void
    {
        $this->tag        = new AuthorTag();
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
        $this->tag->setAuthorName('Foo');
        $this->tag->setAuthorEmail('Bar');
        self::assertSame('Foo', $this->tag->getAuthorName());
        self::assertSame('Bar', $this->tag->getAuthorEmail());
    }

    public function testParamProducesCorrectDocBlockLine()
    {
        $this->tag->setAuthorName('foo');
        $this->tag->setAuthorEmail('string');
        self::assertSame('@author foo <string>', $this->tag->generate());
    }

    public function testNameIsCorrect()
    {
        self::assertSame('author', $this->tag->getName());
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions([
            'authorEmail' => 'string',
            'authorName'  => 'foo',
        ]);
        $tagWithOptionsFromConstructor = new AuthorTag('foo', 'string');
        self::assertSame($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @author Mister Miller <mister.miller@zend.com>');
        $reflectionTag = $docreflection->getTag('author');

        /** @var AuthorTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        self::assertInstanceOf(AuthorTag::class, $tag);
        self::assertSame('Mister Miller', $tag->getAuthorName());
        self::assertSame('mister.miller@zend.com', $tag->getAuthorEmail());
    }
}
