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
use Zend\Code\Generator\DocBlock\Tag\PropertyTag;
use Zend\Code\Generator\DocBlock\TagManager;
use Zend\Code\Reflection\DocBlockReflection;

/**
 * @group Zend_Code_Generator
 * @group Zend_Code_Generator_Php
 */
class PropertyTagTest extends TestCase
{
    /**
     * @var PropertyTag
     */
    protected $tag;

    /**
     * @var TagManager
     */
    protected $tagmanager;

    public function setUp(): void
    {
        $this->tag = new PropertyTag();
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
        $this->tag->setPropertyName('property');
        self::assertEquals('property', $this->tag->getPropertyName());
    }

    public function testGetterForVariableNameTrimsCorrectly()
    {
        $this->tag->setPropertyName('$property$');
        self::assertEquals('property$', $this->tag->getPropertyName());
    }

    public function testNameIsCorrect()
    {
        self::assertEquals('property', $this->tag->getName());
    }

    public function testParamProducesCorrectDocBlockLine()
    {
        $this->tag->setPropertyName('property');
        $this->tag->setTypes('string[]');
        $this->tag->setDescription('description');
        self::assertEquals('@property string[] $property description', $this->tag->generate());
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions([
            'propertyName' => 'property',
            'types' => ['string'],
            'description' => 'description',
        ]);
        $tagWithOptionsFromConstructor = new PropertyTag('property', ['string'], 'description');
        self::assertEquals($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @property int $foo description');
        $reflectionTag = $docreflection->getTag('property');

        /** @var PropertyTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        self::assertInstanceOf(PropertyTag::class, $tag);
        self::assertEquals('foo', $tag->getPropertyName());
        self::assertEquals('description', $tag->getDescription());
        self::assertEquals('int', $tag->getTypesAsString());
    }
}
