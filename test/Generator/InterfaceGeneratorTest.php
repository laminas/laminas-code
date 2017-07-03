<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Generator;

use PHPUnit\Framework\TestCase;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\InterfaceGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Reflection\ClassReflection;
use ZendTest\Code\TestAsset\FooInterface;

/**
 * @group Zend_Code_Generator
 * @group Zend_Code_Generator_Php
 */
class InterfaceGeneratorTest extends TestCase
{
    public function testAbstractAccessorsReturnsFalse()
    {
        $classGenerator = new InterfaceGenerator();
        self::assertFalse($classGenerator->isAbstract());
        $classGenerator->setAbstract(true);
        self::assertFalse($classGenerator->isAbstract());
    }

    public function testExtendedClassAccessors()
    {
        $classGenerator = new InterfaceGenerator();
        $classGenerator->setExtendedClass('ExtendedClass');

        self::assertNull($classGenerator->getExtendedClass());
    }

    public function testImplementedInterfacesAccessors()
    {
        $classGenerator = new InterfaceGenerator();
        $classGenerator->setImplementedInterfaces(['Class1', 'Class2']);

        self::assertCount(2, $classGenerator->getImplementedInterfaces());
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


        self::assertCount(0, $classGenerator1->getProperties());
        self::assertCount(0, $classGenerator2->getProperties());
    }

    public function testMethodAccessors()
    {
        $classGenerator = new InterfaceGenerator();
        $classGenerator->addMethods([
            'methodOne',
            new MethodGenerator('methodTwo')
        ]);

        self::assertCount(2, $classGenerator->getMethods());

        self::assertTrue($classGenerator->getMethod('methodOne')->isInterface());
        self::assertTrue($classGenerator->getMethod('methodTwo')->isInterface());
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
        self::assertEquals($expectedOutput, $output, $output);
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
        self::assertEquals($expected, $classGeneratorClass->generate());
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
        self::assertEquals($expected, $classGeneratorClass->generate());
    }

    /**
     * @group namespace
     */
    public function testCodeGenerationShouldTakeIntoAccountNamespacesFromReflection()
    {
        $reflClass      = new ClassReflection(FooInterface::class);
        $classGenerator = InterfaceGenerator::fromReflection($reflClass);

        self::assertEquals('ZendTest\Code\TestAsset', $classGenerator->getNamespaceName());
        self::assertEquals('FooInterface', $classGenerator->getName());
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
        self::assertEquals($expected, $received, $received);
    }

    /**
     * @group namespace
     */
    public function testSetNameShouldDetermineIfNamespaceSegmentIsPresent()
    {
        $classGeneratorClass = new InterfaceGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        self::assertEquals('My\Namespaced', $classGeneratorClass->getNamespaceName());
    }

    /**
     * @group namespace
     */
    public function testPassingANamespacedClassnameShouldGenerateANamespaceDeclaration()
    {
        $classGeneratorClass = new InterfaceGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        $received = $classGeneratorClass->generate();
        self::assertContains('namespace My\Namespaced;', $received, $received);
    }

    /**
     * @group namespace
     */
    public function testPassingANamespacedClassnameShouldGenerateAClassnameWithoutItsNamespace()
    {
        $classGeneratorClass = new InterfaceGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        $received = $classGeneratorClass->generate();
        self::assertContains('interface FunClass', $received, $received);
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
        self::assertInstanceOf(DocBlockGenerator::class, $docBlock);
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

        self::assertInstanceOf(DocBlockGenerator::class, $docBlock);
        self::assertEquals($expected, $output);
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
        self::assertEquals($expected, $output);
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
        self::assertEquals($expected, $output);
    }

    public function testClassNotAnInterfaceException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class ZendTest\Code\Generator\InterfaceGeneratorTest is not a interface');
        InterfaceGenerator::fromReflection(new ClassReflection(__CLASS__));
    }
}
