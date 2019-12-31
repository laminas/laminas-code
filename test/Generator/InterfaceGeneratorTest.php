<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator;

use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\InterfaceGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Reflection\ClassReflection;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class InterfaceGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testAbstractAccessorsReturnsFalse()
    {
        $classGenerator = new InterfaceGenerator();
        $this->assertFalse($classGenerator->isAbstract());
        $classGenerator->setAbstract(true);
        $this->assertFalse($classGenerator->isAbstract());
    }

    public function testExtendedClassAccessors()
    {
        $classGenerator = new InterfaceGenerator();
        $classGenerator->setExtendedClass('ExtendedClass');

        $this->assertNull($classGenerator->getExtendedClass());
    }

    public function testImplementedInterfacesAccessors()
    {
        $classGenerator = new InterfaceGenerator();
        $classGenerator->setImplementedInterfaces(['Class1', 'Class2']);

        $this->assertCount(2, $classGenerator->getImplementedInterfaces());
    }

    public function testPropertyAccessors()
    {
        $classGenerator1 = new InterfaceGenerator();
        $classGenerator2 = new InterfaceGenerator();

        $classGenerator1->addProperty('prop3');
        $classGenerator2->addProperties([
            'propOne',
            new PropertyGenerator('propTwo')
        ]);


        $this->assertCount(0, $classGenerator1->getProperties());
        $this->assertCount(0, $classGenerator2->getProperties());
    }

    public function testMethodAccessors()
    {
        $classGenerator = new InterfaceGenerator();
        $classGenerator->addMethods([
            'methodOne',
            new MethodGenerator('methodTwo')
        ]);

        $this->assertCount(2, $classGenerator->getMethods());

        $this->assertTrue($classGenerator->getMethod('methodOne')->isInterface());
        $this->assertTrue($classGenerator->getMethod('methodTwo')->isInterface());
    }

    public function testToString()
    {
        $classGenerator = InterfaceGenerator::fromArray([
            'name' => 'SampleInterface',
            'methods' => [
                ['name' => 'baz']
            ],
        ]);

        $expectedOutput = <<<EOS
interface SampleInterface
{

    public function baz();

}

EOS;

        $output = $classGenerator->generate();
        $this->assertEquals($expectedOutput, $output, $output);
    }

    public function testSetextendedclassShouldIgnoreEmptyClassnameOnGenerate()
    {
        $classGeneratorClass = new InterfaceGenerator();
        $classGeneratorClass
            ->setName('MyInterface')
            ->setExtendedClass('');

        $expected = <<<CODE
interface MyInterface
{


}

CODE;
        $this->assertEquals($expected, $classGeneratorClass->generate());
    }


    public function testSetextendedclassShouldNotIgnoreNonEmptyClassnameOnGenerate()
    {
        $classGeneratorClass = new InterfaceGenerator();
        $classGeneratorClass
            ->setName('MyInterface')
            ->setExtendedClass('MyInterface');

        $expected = <<<CODE
interface MyInterface
{


}

CODE;
        $this->assertEquals($expected, $classGeneratorClass->generate());
    }

    /**
     * @group namespace
     */
    public function testCodeGenerationShouldTakeIntoAccountNamespacesFromReflection()
    {
        $reflClass      = new ClassReflection('LaminasTest\Code\TestAsset\FooInterface');
        $classGenerator = InterfaceGenerator::fromReflection($reflClass);

        $this->assertEquals('LaminasTest\Code\TestAsset', $classGenerator->getNamespaceName());
        $this->assertEquals('FooInterface', $classGenerator->getName());
        $expected = <<<CODE
namespace LaminasTest\Code\TestAsset;

interface FooInterface
{

    const BAR = 5;

    const FOO = 5;

    public function fooBarBaz();

}

CODE;
        $received = $classGenerator->generate();
        $this->assertEquals($expected, $received, $received);
    }

    /**
     * @group namespace
     */
    public function testSetNameShouldDetermineIfNamespaceSegmentIsPresent()
    {
        $classGeneratorClass = new InterfaceGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        $this->assertEquals('My\Namespaced', $classGeneratorClass->getNamespaceName());
    }

    /**
     * @group namespace
     */
    public function testPassingANamespacedClassnameShouldGenerateANamespaceDeclaration()
    {
        $classGeneratorClass = new InterfaceGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        $received = $classGeneratorClass->generate();
        $this->assertContains('namespace My\Namespaced;', $received, $received);
    }

    /**
     * @group namespace
     */
    public function testPassingANamespacedClassnameShouldGenerateAClassnameWithoutItsNamespace()
    {
        $classGeneratorClass = new InterfaceGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        $received = $classGeneratorClass->generate();
        $this->assertContains('interface FunClass', $received, $received);
    }

    public function testCreateFromArrayWithDocBlockFromArray()
    {
        $classGenerator = InterfaceGenerator::fromArray([
            'name' => 'SampleClass',
            'docblock' => [
                'shortdescription' => 'foo',
            ],
        ]);

        $docBlock = $classGenerator->getDocBlock();
        $this->assertInstanceOf('Laminas\Code\Generator\DocBlockGenerator', $docBlock);
    }

    public function testCreateFromArrayWithDocBlockInstance()
    {
        $classGenerator = InterfaceGenerator::fromArray([
            'name' => 'MyInterface',
            'docblock' => new DocBlockGenerator('foo'),
        ]);

        $docBlock = $classGenerator->getDocBlock();
        $output   = $classGenerator->generate();

        $expected = <<<CODE
/**
 * foo
 */
interface MyInterface
{


}

CODE;

        $this->assertInstanceOf('Laminas\Code\Generator\DocBlockGenerator', $docBlock);
        $this->assertEquals($expected, $output);
    }

    public function testGenerateClassAndAddMethod()
    {
        $classGenerator = new InterfaceGenerator();
        $classGenerator->setName('MyInterface');
        $classGenerator->addMethod('methodOne');

        $expected = <<<CODE
interface MyInterface
{

    public function methodOne();

}

CODE;

        $output = $classGenerator->generate();
        $this->assertEquals($expected, $output);
    }

    public function testGenerateImplementsInterface()
    {
        $classGenerator = new InterfaceGenerator();
        $classGenerator->setName('MyCollection');
        $classGenerator->addMethod('isEmpty');

        $classGenerator->setImplementedInterfaces(['Countable', 'IteratorAggregate']);

        $expected = <<<CODE
interface MyCollection extends Countable, IteratorAggregate
{

    public function isEmpty();

}

CODE;

        $output = $classGenerator->generate();
        $this->assertEquals($expected, $output);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Class LaminasTest\Code\Generator\InterfaceGeneratorTest is not a interface
     */
    public function testClassNotAnInterfaceException()
    {
        InterfaceGenerator::fromReflection(new ClassReflection(__CLASS__));
    }
}
