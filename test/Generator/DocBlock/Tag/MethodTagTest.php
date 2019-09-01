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
use Zend\Code\Generator\DocBlock\Tag\MethodTag;
use Zend\Code\Generator\DocBlock\TagManager;
use Zend\Code\Reflection\DocBlockReflection;

/**
 * @group Zend_Code_Generator
 * @group Zend_Code_Generator_Php
 */
class MethodTagTest extends TestCase
{
    /**
     * @var MethodTag
     */
    protected $tag;

    /**
     * @var TagManager
     */
    protected $tagmanager;

    public function setUp(): void
    {
        $this->tag = new MethodTag();
        $this->tagmanager = new TagManager();
        $this->tagmanager->initializeDefaultTags();
    }

    public function tearDown(): void
    {
        $this->tag = null;
        $this->tagmanager = null;
    }

    public function testGetterAndSetterPersistValue()
    {
        $this->tag->setIsStatic(true);
        $this->tag->setMethodName('method');
        self::assertEquals(true, $this->tag->isStatic());
        self::assertEquals('method', $this->tag->getMethodName());
    }

    public function testGetterForMethodNameTrimsCorrectly()
    {
        $this->tag->setMethodName('()method()');
        self::assertEquals('()method', $this->tag->getMethodName());
    }

    public function testNameIsCorrect()
    {
        self::assertEquals('method', $this->tag->getName());
    }

    public function testParamProducesCorrectDocBlockLine()
    {
        $this->tag->setIsStatic(true);
        $this->tag->setMethodName('method');
        $this->tag->setTypes('int');
        $this->tag->setDescription('method(string $a)');
        self::assertEquals('@method static int method() method(string $a)', $this->tag->generate());
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions([
            'isStatic' => true,
            'methodName' => 'method',
            'types' => ['string'],
            'description' => 'description',
        ]);
        $tagWithOptionsFromConstructor = new MethodTag('method', ['string'], 'description', true);
        self::assertEquals($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @method static int method() method(int $a)');
        $reflectionTag = $docreflection->getTag('method');

        /** @var MethodTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        self::assertInstanceOf(MethodTag::class, $tag);
        self::assertEquals(true, $tag->isStatic());
        self::assertEquals('int', $tag->getTypesAsString());
        self::assertEquals('method', $tag->getMethodName());
        self::assertEquals('method(int $a)', $tag->getDescription());
    }
}
