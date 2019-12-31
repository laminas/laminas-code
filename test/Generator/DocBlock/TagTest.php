<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator\DocBlock;

use Laminas\Code\Generator\DocBlock\Tag;
use Laminas\Code\Generator\DocBlock\Tag\LicenseTag;

/**
 * @category   Laminas
 * @package    Laminas_Code_Generator
 * @subpackage UnitTests
 *
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class TagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Tag
     */
    protected $tag;

    public function setUp()
    {
        $this->tag = new Tag();
    }

    public function tearDown()
    {
        $this->tag = null;
    }

    public function testCanPassNameToConstructor()
    {
        $tag = new Tag(array('name' => 'Foo'));
        $this->assertEquals('Foo', $tag->getName());
    }

    public function testCanPassDescriptionToConstructor()
    {
        $tag = new Tag(array('description' => 'Foo'));
        $this->assertEquals('Foo', $tag->getDescription());
    }

    public function testCanGenerateLicenseTag()
    {
        $tag = new LicenseTag(array(
            'url'         => 'http://test.license.com',
            'description' => 'Test License',
        ));
        $this->assertEquals(
            '@license http://test.license.com Test License',
            $tag->generate()
        );
    }

    public function testNameGetterAndSetterPersistValue()
    {
        $this->tag->setName('Foo');
        $this->assertEquals('Foo', $this->tag->getName());
    }

    public function testDescriptionGetterAndSetterPersistValue()
    {
        $this->tag->setDescription('Foo foo foo');
        $this->assertEquals('Foo foo foo', $this->tag->getDescription());
    }

    public function testParamProducesCorrectDocBlockTag()
    {
        $this->tag->setName('foo');
        $this->tag->setDescription('bar bar bar');
        $this->assertEquals('@foo bar bar bar', $this->tag->generate());
    }
}
