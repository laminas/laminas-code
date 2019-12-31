<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag\ParamTag;

/**
 * @category   Laminas
 * @package    Laminas_Code_Generator
 * @subpackage UnitTests
 *
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class ParamTagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ParamTag
     */
    protected $tag;

    public function setUp()
    {
        $this->tag = new ParamTag();
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

    public function testParamNameGetterAndSetterPersistValue()
    {
        $this->tag->setParamName('Foo');
        $this->assertEquals('Foo', $this->tag->getParamName());
    }

    public function testParamProducesCorrectDocBlockLine()
    {
        $this->tag->setParamName('foo');
        $this->tag->setDatatype('string');
        $this->tag->setDescription('bar bar bar');
        $this->assertEquals('@param string $foo bar bar bar', $this->tag->generate());
    }
}
