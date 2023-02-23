<?php

namespace LaminasTest\Code\Generator;

use Countable;
use DateTime;
use IteratorAggregate;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\InterfaceGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Reflection\ClassReflection;
use LaminasTest\Code\TestAsset\FooInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Laminas_Code_Generator')]
#[Group('Laminas_Code_Generator_Php')]
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
        $classGenerator->setExtendedClass(DateTime::class);

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
            new PropertyGenerator('propTwo'),
        ]);

        self::assertCount(0, $classGenerator1->getProperties());
        self::assertCount(0, $classGenerator2->getProperties());
    }

    public function testMethodAccessors()
    {
        $classGenerator = new InterfaceGenerator();
        $classGenerator->addMethods([
            'methodOne',
            new MethodGenerator('methodTwo'),
        ]);

        self::assertCount(2, $classGenerator->getMethods());

        self::assertTrue($classGenerator->getMethod('methodOne')->isInterface());
        self::assertTrue($classGenerator->getMethod('methodTwo')->isInterface());
    }

    public function testToString()
    {
        $classGenerator = InterfaceGenerator::fromArray([
            'name'    => 'SampleInterface',
            'methods' => [
                ['name' => 'baz'],
            ],
        ]);

        $expectedOutput = <<<EOS
interface SampleInterface
{
    public function baz();
}

EOS;

        self::assertSame($expectedOutput, $classGenerator->generate());
    }

    public function testSetextendedclassShouldIgnoreEmptyClassnameOnGenerate()
    {
        $classGeneratorClass = new InterfaceGenerator();
        $classGeneratorClass
            ->setName('MyInterface')
            ->setExtendedClass(null);

        $expected = <<<CODE
interface MyInterface
{
}

CODE;
        self::assertSame($expected, $classGeneratorClass->generate());
    }

    public function testSetextendedclassShouldNotIgnoreNonEmptyClassnameOnGenerate()
    {
        $classGeneratorClass = new InterfaceGenerator();
        $classGeneratorClass
            ->setName('MyInterface')
            ->setExtendedClass(DateTime::class);

        $expected = <<<CODE
interface MyInterface
{
}

CODE;
        self::assertSame($expected, $classGeneratorClass->generate());
    }

    #[Group('namespace')]
    public function testCodeGenerationShouldTakeIntoAccountNamespacesFromReflection()
    {
        $reflClass      = new ClassReflection(FooInterface::class);
        $classGenerator = InterfaceGenerator::fromReflection($reflClass);

        self::assertSame('LaminasTest\Code\TestAsset', $classGenerator->getNamespaceName());
        self::assertSame('FooInterface', $classGenerator->getName());
        $expected = <<<CODE
namespace LaminasTest\Code\TestAsset;

interface FooInterface
{
    public const BAR = 5;

    public const FOO = 5;

    public function fooBarBaz();
}

CODE;
        $received = $classGenerator->generate();
        self::assertSame($expected, $received, $received);
    }

    #[Group('namespace')]
    public function testSetNameShouldDetermineIfNamespaceSegmentIsPresent()
    {
        $classGeneratorClass = new InterfaceGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        self::assertSame('My\Namespaced', $classGeneratorClass->getNamespaceName());
    }

    #[Group('namespace')]
    public function testPassingANamespacedClassnameShouldGenerateANamespaceDeclaration()
    {
        $classGeneratorClass = new InterfaceGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        $received = $classGeneratorClass->generate();
        self::assertStringContainsString('namespace My\Namespaced;', $received, $received);
    }

    #[Group('namespace')]
    public function testPassingANamespacedClassnameShouldGenerateAClassnameWithoutItsNamespace()
    {
        $classGeneratorClass = new InterfaceGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        $received = $classGeneratorClass->generate();
        self::assertStringContainsString('interface FunClass', $received, $received);
    }

    public function testCreateFromArrayWithDocBlockFromArray()
    {
        $classGenerator = InterfaceGenerator::fromArray([
            'name'     => 'SampleClass',
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
            'name'     => 'MyInterface',
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
        self::assertSame($expected, $output);
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
        self::assertSame($expected, $output);
    }

    public function testGenerateImplementsInterface()
    {
        $classGenerator = new InterfaceGenerator();
        $classGenerator->setName('MyCollection');
        $classGenerator->addMethod('isEmpty');

        $classGenerator->setImplementedInterfaces([Countable::class, IteratorAggregate::class]);

        $expected = <<<CODE
interface MyCollection extends Countable, IteratorAggregate
{
    public function isEmpty();
}

CODE;

        $output = $classGenerator->generate();
        self::assertSame($expected, $output);
    }

    public function testClassNotAnInterfaceException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class LaminasTest\Code\Generator\InterfaceGeneratorTest is not a interface');
        InterfaceGenerator::fromReflection(new ClassReflection(self::class));
    }
}
