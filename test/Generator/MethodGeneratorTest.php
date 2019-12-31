<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator;

use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\ValueGenerator;
use Laminas\Code\Reflection\MethodReflection;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class MethodGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testMethodConstructor()
    {
        $methodGenerator = new MethodGenerator();
        $this->isInstanceOf($methodGenerator, '\Laminas\Code\Generator\PhpMethod');
    }

    public function testMethodParameterAccessors()
    {
        $methodGenerator = new MethodGenerator();
        $methodGenerator->setParameters(array('one'));
        $params = $methodGenerator->getParameters();
        $param = array_shift($params);
        $this->assertTrue($param instanceof \Laminas\Code\Generator\ParameterGenerator,
                          'Failed because $param was not instance of Laminas\Code\Generator\ParameterGenerator');
    }

    public function testMethodBodyGetterAndSetter()
    {
        $method = new MethodGenerator();
        $method->setBody('Foo');
        $this->assertEquals('Foo', $method->getBody());
    }

    public function testDocBlockGetterAndSetter()
    {
        $docblockGenerator = new \Laminas\Code\Generator\DocBlockGenerator();

        $method = new MethodGenerator();
        $method->setDocBlock($docblockGenerator);
        $this->assertTrue($docblockGenerator === $method->getDocBlock());
    }


    public function testMethodFromReflection()
    {
        $ref = new MethodReflection('LaminasTest\Code\Generator\TestAsset\TestSampleSingleClass', 'someMethod');

        $methodGenerator = MethodGenerator::fromReflection($ref);
        $target = <<<EOS
    /**
     * Enter description here...
     *
     * @return bool
     */
    public function someMethod()
    {
        /* test test */
    }

EOS;
        $this->assertEquals($target, (string) $methodGenerator);
    }

    /**
     * @group Laminas-6444
     */
    public function testMethodWithStaticModifierIsEmitted()
    {
        $methodGenerator = new MethodGenerator();
        $methodGenerator->setName('foo');
        $methodGenerator->setParameters(array('one'));
        $methodGenerator->setStatic(true);

        $expected = <<<EOS
    public static function foo(\$one)
    {
    }

EOS;

        $this->assertEquals($expected, $methodGenerator->generate());
    }

    /**
     * @group Laminas-6444
     */
    public function testMethodWithFinalModifierIsEmitted()
    {
        $methodGenerator = new MethodGenerator();
        $methodGenerator->setName('foo');
        $methodGenerator->setParameters(array('one'));
        $methodGenerator->setFinal(true);

        $expected = <<<EOS
    final public function foo(\$one)
    {
    }

EOS;
        $this->assertEquals($expected, $methodGenerator->generate());
    }

    /**
     * @group Laminas-6444
     */
    public function testMethodWithFinalModifierIsNotEmittedWhenMethodIsAbstract()
    {
        $methodGenerator = new MethodGenerator();
        $methodGenerator->setName('foo');
        $methodGenerator->setParameters(array('one'));
        $methodGenerator->setFinal(true);
        $methodGenerator->setAbstract(true);

        $expected = <<<EOS
    abstract public function foo(\$one);
EOS;
        $this->assertEquals($expected, $methodGenerator->generate());
    }

    /**
     * @group Laminas-7205
     */
    public function testMethodCanHaveDocBlock()
    {
        $methodGeneratorProperty = new MethodGenerator(
            'someFoo',
            array(),
            MethodGenerator::FLAG_STATIC | MethodGenerator::FLAG_PROTECTED,
            null,
            '@var string $someVal This is some val'
        );

        $expected = <<<EOS
    /**
     * @var string \$someVal This is some val
     */
    protected static function someFoo()
    {
    }

EOS;
        $this->assertEquals($expected, $methodGeneratorProperty->generate());
    }

    /**
     * @group Laminas-7268
     */
    public function testDefaultValueGenerationDoesNotIncludeTrailingSemicolon()
    {
        $method = new MethodGenerator('setOptions');
        $default = new ValueGenerator();
        $default->setValue(array());

        $param   = new ParameterGenerator('options', 'array');
        $param->setDefaultValue($default);

        $method->setParameter($param);
        $generated = $method->generate();
        $this->assertRegexp('/array \$options = array\(\)\)/', $generated, $generated);
    }

    public function testCreateFromArray()
    {
        $methodGenerator = MethodGenerator::fromArray(array(
            'name'       => 'SampleMethod',
            'body'       => 'foo',
            'docblock'   => array(
                'shortdescription' => 'foo',
            ),
            'abstract'   => true,
            'final'      => true,
            'static'     => true,
            'visibility' => MethodGenerator::VISIBILITY_PROTECTED,
        ));

        $this->assertEquals('SampleMethod', $methodGenerator->getName());
        $this->assertEquals('foo', $methodGenerator->getBody());
        $this->assertInstanceOf('Laminas\Code\Generator\DocBlockGenerator', $methodGenerator->getDocBlock());
        $this->assertTrue($methodGenerator->isAbstract());
        $this->assertTrue($methodGenerator->isFinal());
        $this->assertTrue($methodGenerator->isStatic());
        $this->assertEquals(MethodGenerator::VISIBILITY_PROTECTED, $methodGenerator->getVisibility());
    }
}
