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
use ZendTest\Code\Generator\TestAsset\TypeableTag;

/**
 * @group Zend_Code_Generator
 * @group Zend_Code_Generator_Php
 */
class TypableTagTest extends TestCase
{
    /**
     * @var TypeableTag
     */
    protected $tag;

    public function setUp()
    {
        $this->tag = new TypeableTag();
    }

    public function tearDown()
    {
        $this->tag = null;
    }

    public function testGetterAndSetterPersistValue()
    {
        $this->tag->setTypes(['string', 'null']);
        $this->tag->setDescription('Description');
        self::assertEquals(['string', 'null'], $this->tag->getTypes());
        self::assertEquals('Description', $this->tag->getDescription());
    }

    public function testGetterForTypesAsStringWithSingleType()
    {
        $this->tag->setTypes(['string']);
        self::assertEquals('string', $this->tag->getTypesAsString());
    }

    public function testGetterForTypesAsStringWithSingleTypeAndDelimiter()
    {
        $this->tag->setTypes(['string']);
        self::assertEquals('string', $this->tag->getTypesAsString('/'));
    }

    public function testGetterForTypesAsStringWithMultipleTypes()
    {
        $this->tag->setTypes(['string', 'null']);
        self::assertEquals('string|null', $this->tag->getTypesAsString());
    }

    public function testGetterForTypesAsStringWithMultipleTypesAndDelimiter()
    {
        $this->tag->setTypes(['string', 'null']);
        self::assertEquals('string/null', $this->tag->getTypesAsString('/'));
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions([
            'types' => ['string', 'null'],
            'description' => 'description',
        ]);
        $tagWithOptionsFromConstructor = new TypeableTag(['string', 'null'], 'description');
        self::assertEquals($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }
}
