<?php

/**
 * @see       https://github.com/laminasframwork/laminas-code for the canonical source repository
 * @copyright https://github.com/laminasframwork/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminasframwork/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag\AuthorTag;
use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlockReflection;
use PHPUnit\Framework\TestCase;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class AuthorTagTest extends TestCase
{
    /**
     * @var AuthorTag
     */
    protected $tag;

    /**
     * @var TagManager
     */
    protected $tagmanager;

    protected function setUp() : void
    {
        $this->tag = new AuthorTag();
        $this->tagmanager = new TagManager();
        $this->tagmanager->initializeDefaultTags();
    }

    protected function tearDown() : void
    {
        $this->tag = null;
        $this->tagmanager = null;
    }

    public function testGetterAndSetterPersistValue()
    {
        $this->tag->setAuthorName('Foo');
        $this->tag->setAuthorEmail('Bar');
        self::assertEquals('Foo', $this->tag->getAuthorName());
        self::assertEquals('Bar', $this->tag->getAuthorEmail());
    }

    public function testParamProducesCorrectDocBlockLine()
    {
        $this->tag->setAuthorName('foo');
        $this->tag->setAuthorEmail('string');
        self::assertEquals('@author foo <string>', $this->tag->generate());
    }

    public function testNameIsCorrect()
    {
        self::assertEquals('author', $this->tag->getName());
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions([
            'authorEmail' => 'string',
            'authorName' => 'foo',
        ]);
        $tagWithOptionsFromConstructor = new AuthorTag('foo', 'string');
        self::assertEquals($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @author Mister Miller <mister.miller@zend.com>');
        $reflectionTag = $docreflection->getTag('author');

        /** @var AuthorTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        self::assertInstanceOf(AuthorTag::class, $tag);
        self::assertEquals('Mister Miller', $tag->getAuthorName());
        self::assertEquals('mister.miller@zend.com', $tag->getAuthorEmail());
    }
}
