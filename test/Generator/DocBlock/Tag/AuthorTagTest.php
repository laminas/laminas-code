<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Generator\DocBlock\Tag;

use PHPUnit\Framework\TestCase;
use Zend\Code\Generator\DocBlock\Tag\AuthorTag;
use Zend\Code\Generator\DocBlock\TagManager;
use Zend\Code\Reflection\DocBlockReflection;

/**
 * @group Zend_Code_Generator
 * @group Zend_Code_Generator_Php
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

    public function setUp()
    {
        $this->tag = new AuthorTag();
        $this->tagmanager = new TagManager();
        $this->tagmanager->initializeDefaultTags();
    }

    public function tearDown()
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
