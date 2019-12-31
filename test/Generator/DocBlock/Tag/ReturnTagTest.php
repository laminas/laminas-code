<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag\ReturnTag;

/**
 * @category   Laminas
 * @package    Laminas_Code_Generator
 * @subpackage UnitTests
 *
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class ReturnTagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReturnTag
     */
    protected $tag;

    public function setUp()
    {
        $this->tag = new ReturnTag();
    }

    public function tearDown()
    {
        $this->tag = null;
    }

    public function testDatatypeGetterAndSetterPersistValue()
    {
        $this->tag->setDatatype('Foo');
        $this->assertEquals('Foo', $this->tag->getDatatype());
    }

    public function testReturnProducesCorrectDocBlockLine()
    {
        $this->tag->setDatatype('string');
        $this->tag->setDescription('bar bar bar');
        $this->assertEquals('@return string bar bar bar', $this->tag->generate());
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions(array(
            'datatype' => 'string|null',
        ));
        $tagWithOptionsFromConstructor = new ReturnTag(array(
            'datatype' => 'string|null',
        ));
        $this->assertEquals($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }
}
