<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Generator;

use Zend\Code\Generator\InterfaceGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Reflection\ClassReflection;

/**
 * @group Zend_Code_Generator
 * @group Zend_Code_Generator_Php
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
        $reflClass      = new ClassReflection('ZendTest\Code\TestAsset\FooInterface');
        $classGenerator = InterfaceGenerator::fromReflection($reflClass);

        $this->assertEquals('ZendTest\Code\TestAsset', $classGenerator->getNamespaceName());
        $this->assertEquals('FooInterface', $classGenerator->getName());
        $expected = <<<CODE
namespace ZendTest\Code\TestAsset;

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
        $this->assertInstanceOf('Zend\Code\Generator\DocBlockGenerator', $docBlock);
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

        $this->assertInstanceOf('Zend\Code\Generator\DocBlockGenerator', $docBlock);
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
     * @expectedExceptionMessage Class ZendTest\Code\Generator\InterfaceGeneratorTest is not a interface
     */
    public function testClassNotAnInterfaceException()
    {
        InterfaceGenerator::fromReflection(new ClassReflection(__CLASS__));
    }
}
