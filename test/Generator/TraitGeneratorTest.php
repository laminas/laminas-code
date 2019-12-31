<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator;

use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\TraitGenerator;
use Laminas\Code\Reflection\ClassReflection;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class TraitGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (version_compare(PHP_VERSION, '5.4', 'lt')) {
            $this->markTestSkipped('This test requires PHP version 5.4+');
        }
    }

    public function testConstruction()
    {
        $class = new TraitGenerator();
        $this->isInstanceOf($class, 'Laminas\Code\Generator\TraitGenerator');
    }

    public function testNameAccessors()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setName('TestClass');
        $this->assertEquals($classGenerator->getName(), 'TestClass');
    }

    public function testClassDocBlockAccessors()
    {
        $this->markTestIncomplete();
    }

    public function testAbstractAccessorsReturnsFalse()
    {
        $classGenerator = new TraitGenerator();
        $this->assertFalse($classGenerator->isAbstract());
        $classGenerator->setAbstract(true);
        $this->assertFalse($classGenerator->isAbstract());
    }

    public function testExtendedClassAccessors()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setExtendedClass('ExtendedClass');
        $this->assertEquals($classGenerator->getExtendedClass(), null);
    }

    public function testImplementedInterfacesAccessors()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setImplementedInterfaces(array('Class1', 'Class2'));
        $this->assertEquals(count($classGenerator->getImplementedInterfaces()), 0);
    }

    public function testPropertyAccessors()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addProperties(array(
            'propOne',
            new PropertyGenerator('propTwo')
        ));

        $properties = $classGenerator->getProperties();
        $this->assertEquals(count($properties), 2);
        $this->assertInstanceOf('Laminas\Code\Generator\PropertyGenerator', current($properties));

        $property = $classGenerator->getProperty('propTwo');
        $this->assertInstanceOf('Laminas\Code\Generator\PropertyGenerator', $property);
        $this->assertEquals($property->getName(), 'propTwo');

        // add a new property
        $classGenerator->addProperty('prop3');
        $this->assertEquals(count($classGenerator->getProperties()), 3);
    }

    public function testSetPropertyAlreadyExistsThrowsException()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addProperty('prop3');

        $this->setExpectedException(
            'Laminas\Code\Generator\Exception\InvalidArgumentException',
            'A property by name prop3 already exists in this class'
        );
        $classGenerator->addProperty('prop3');
    }

    public function testSetPropertyNoArrayOrPropertyThrowsException()
    {
        $classGenerator = new TraitGenerator();

        $this->setExpectedException(
            'Laminas\Code\Generator\Exception\InvalidArgumentException',
            'Laminas\Code\Generator\TraitGenerator::addProperty expects string for name'
        );
        $classGenerator->addProperty(true);
    }

    public function testMethodAccessors()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addMethods(array(
            'methodOne',
            new MethodGenerator('methodTwo')
        ));

        $methods = $classGenerator->getMethods();
        $this->assertEquals(count($methods), 2);
        $this->isInstanceOf(current($methods), '\Laminas\Code\Generator\PhpMethod');

        $method = $classGenerator->getMethod('methodOne');
        $this->isInstanceOf($method, '\Laminas\Code\Generator\PhpMethod');
        $this->assertEquals($method->getName(), 'methodOne');

        // add a new property
        $classGenerator->addMethod('methodThree');
        $this->assertEquals(count($classGenerator->getMethods()), 3);
    }

    public function testSetMethodNoMethodOrArrayThrowsException()
    {
        $classGenerator = new TraitGenerator();

        $this->setExpectedException(
            'Laminas\Code\Generator\Exception\ExceptionInterface',
            'Laminas\Code\Generator\TraitGenerator::addMethod expects string for name'
        );

        $classGenerator->addMethod(true);
    }

    public function testSetMethodNameAlreadyExistsThrowsException()
    {
        $methodA = new MethodGenerator();
        $methodA->setName("foo");
        $methodB = new MethodGenerator();
        $methodB->setName("foo");

        $classGenerator = new TraitGenerator();
        $classGenerator->addMethodFromGenerator($methodA);

        $this->setExpectedException(
            'Laminas\Code\Generator\Exception\InvalidArgumentException',
            'A method by name foo already exists in this class.'
        );

        $classGenerator->addMethodFromGenerator($methodB);
    }

    /**
     * @group Laminas-7361
     */
    public function testHasMethod()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addMethod('methodOne');

        $this->assertTrue($classGenerator->hasMethod('methodOne'));
    }

    public function testRemoveMethod()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addMethod('methodOne');
        $this->assertTrue($classGenerator->hasMethod('methodOne'));

        $classGenerator->removeMethod('methodOne');
        $this->assertFalse($classGenerator->hasMethod('methodOne'));
    }

    /**
     * @group Laminas-7361
     */
    public function testHasProperty()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addProperty('propertyOne');

        $this->assertTrue($classGenerator->hasProperty('propertyOne'));
    }

    public function testToString()
    {
        $classGenerator = TraitGenerator::fromArray(array(
            'name' => 'SampleClass',
            'properties' => array('foo',
                array('name' => 'bar')
            ),
            'methods' => array(
                array('name' => 'baz')
            ),
        ));

        $expectedOutput = <<<EOS
trait SampleClass
{

    public \$foo = null;

    public \$bar = null;

    public function baz()
    {
    }


}

EOS;

        $output = $classGenerator->generate();
        $this->assertEquals($expectedOutput, $output, $output);
    }

    /**
     * @group Laminas-7909
     */
    public function testClassFromReflectionThatImplementsInterfaces()
    {
        $reflClass = new ClassReflection('LaminasTest\Code\Generator\TestAsset\ClassWithInterface');

        $classGenerator = TraitGenerator::fromReflection($reflClass);
        $classGenerator->setSourceDirty(true);

        $code = $classGenerator->generate();

        $expectedClassDef = 'trait ClassWithInterface';
        $this->assertContains($expectedClassDef, $code);
    }

    /**
     * @group Laminas-7909
     */
    public function testClassFromReflectionDiscardParentImplementedInterfaces()
    {
        $reflClass = new ClassReflection('LaminasTest\Code\Generator\TestAsset\NewClassWithInterface');

        $classGenerator = TraitGenerator::fromReflection($reflClass);
        $classGenerator->setSourceDirty(true);

        $code = $classGenerator->generate();

        $expectedClassDef = 'trait NewClassWithInterface';
        $this->assertContains($expectedClassDef, $code);
    }

    /**
     * @group 4988
     */
    public function testNonNamespaceClassReturnsAllMethods()
    {
        require_once __DIR__ . '/../TestAsset/NonNamespaceClass.php';

        $reflClass = new ClassReflection('LaminasTest_Code_NsTest_BarClass');
        $classGenerator = TraitGenerator::fromReflection($reflClass);
        $this->assertCount(1, $classGenerator->getMethods());
    }

    /**
     * @group Laminas-9602
     */
    public function testSetextendedclassShouldIgnoreEmptyClassnameOnGenerate()
    {
        $classGeneratorClass = new TraitGenerator();
        $classGeneratorClass
            ->setName('MyClass')
            ->setExtendedClass('');

        $expected = <<<CODE
trait MyClass
{


}

CODE;
        $this->assertEquals($expected, $classGeneratorClass->generate());
    }

    /**
     * @group Laminas-9602
     */
    public function testSetextendedclassShouldNotIgnoreNonEmptyClassnameOnGenerate()
    {
        $classGeneratorClass = new TraitGenerator();
        $classGeneratorClass
            ->setName('MyClass')
            ->setExtendedClass('ParentClass');

        $expected = <<<CODE
trait MyClass
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
        $reflClass = new ClassReflection('LaminasTest\Code\Generator\TestAsset\ClassWithNamespace');
        $classGenerator = TraitGenerator::fromReflection($reflClass);
        $this->assertEquals('LaminasTest\Code\Generator\TestAsset', $classGenerator->getNamespaceName());
        $this->assertEquals('ClassWithNamespace', $classGenerator->getName());
        $expected = <<<CODE
namespace LaminasTest\Code\Generator\\TestAsset;

trait ClassWithNamespace
{


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
        $classGeneratorClass = new TraitGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        $this->assertEquals('My\Namespaced', $classGeneratorClass->getNamespaceName());
    }

    /**
     * @group namespace
     */
    public function testPassingANamespacedClassnameShouldGenerateANamespaceDeclaration()
    {
        $classGeneratorClass = new TraitGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        $received = $classGeneratorClass->generate();
        $this->assertContains('namespace My\Namespaced;', $received, $received);
    }

    /**
     * @group namespace
     */
    public function testPassingANamespacedClassnameShouldGenerateAClassnameWithoutItsNamespace()
    {
        $classGeneratorClass = new TraitGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        $received = $classGeneratorClass->generate();
        $this->assertContains('trait FunClass', $received, $received);
    }

    /**
     * @group Laminas-151
     */
    public function testAddUses()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setName('My\Class');
        $classGenerator->addUse('My\First\Use\Class');
        $classGenerator->addUse('My\Second\Use\Class', 'MyAlias');
        $generated = $classGenerator->generate();

        $this->assertContains('use My\First\Use\Class;', $generated);
        $this->assertContains('use My\Second\Use\Class as MyAlias;', $generated);
    }

    /**
     * @group 4990
     */
    public function testAddOneUseTwiceOnlyAddsOne()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setName('My\Class');
        $classGenerator->addUse('My\First\Use\Class');
        $classGenerator->addUse('My\First\Use\Class');
        $generated = $classGenerator->generate();

        $this->assertCount(1, $classGenerator->getUses());

        $this->assertContains('use My\First\Use\Class;', $generated);
    }

    /**
     * @group 4990
     */
    public function testAddOneUseWithAliasTwiceOnlyAddsOne()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setName('My\Class');
        $classGenerator->addUse('My\First\Use\Class', 'MyAlias');
        $classGenerator->addUse('My\First\Use\Class', 'MyAlias');
        $generated = $classGenerator->generate();

        $this->assertCount(1, $classGenerator->getUses());

        $this->assertContains('use My\First\Use\Class as MyAlias;', $generated);
    }

    public function testCreateFromArrayWithDocBlockFromArray()
    {
        $classGenerator = TraitGenerator::fromArray(array(
            'name' => 'SampleClass',
            'docblock' => array(
                'shortdescription' => 'foo',
            ),
        ));

        $docBlock = $classGenerator->getDocBlock();
        $this->assertInstanceOf('Laminas\Code\Generator\DocBlockGenerator', $docBlock);
    }

    public function testCreateFromArrayWithDocBlockInstance()
    {
        $classGenerator = TraitGenerator::fromArray(array(
            'name' => 'SampleClass',
            'docblock' => new DocBlockGenerator('foo'),
        ));

        $docBlock = $classGenerator->getDocBlock();
        $this->assertInstanceOf('Laminas\Code\Generator\DocBlockGenerator', $docBlock);
    }

    public function testExtendedClassProperies()
    {
        $reflClass = new ClassReflection('LaminasTest\Code\Generator\TestAsset\ExtendedClassWithProperties');
        $classGenerator = TraitGenerator::fromReflection($reflClass);
        $code = $classGenerator->generate();
        $this->assertContains('publicExtendedClassProperty', $code);
        $this->assertContains('protectedExtendedClassProperty', $code);
        $this->assertContains('privateExtendedClassProperty', $code);
        $this->assertNotContains('publicClassProperty', $code);
        $this->assertNotContains('protectedClassProperty', $code);
        $this->assertNotContains('privateClassProperty', $code);
    }

    public function testHasMethodInsensitive()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addMethod('methodOne');

        $this->assertTrue($classGenerator->hasMethod('methodOne'));
        $this->assertTrue($classGenerator->hasMethod('MethoDonE'));
    }

    public function testRemoveMethodInsensitive()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addMethod('methodOne');

        $classGenerator->removeMethod('METHODONe');
        $this->assertFalse($classGenerator->hasMethod('methodOne'));
    }

    public function testGenerateClassAndAddMethod()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setName('MyClass');
        $classGenerator->addMethod('methodOne');

        $expected = <<<CODE
trait MyClass
{

    public function methodOne()
    {
    }


}

CODE;

        $output = $classGenerator->generate();
        $this->assertEquals($expected, $output);
    }
}
