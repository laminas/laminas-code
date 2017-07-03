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
use Zend\Code\Generator\Exception\ExceptionInterface;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\TraitGenerator;
use Zend\Code\Reflection\ClassReflection;

/**
 * @group Zend_Code_Generator
 * @group Zend_Code_Generator_Php
 */
class TraitGeneratorTest extends TestCase
{
    public function setUp()
    {
    }

    public function testConstruction()
    {
        $class = new TraitGenerator();
        self::assertInstanceOf(TraitGenerator::class, $class);
    }

    public function testNameAccessors()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setName('TestClass');
        self::assertEquals($classGenerator->getName(), 'TestClass');
    }

    public function testClassDocBlockAccessors()
    {
        $this->markTestIncomplete();
    }

    public function testAbstractAccessorsReturnsFalse()
    {
        $classGenerator = new TraitGenerator();
        self::assertFalse($classGenerator->isAbstract());
        $classGenerator->setAbstract(true);
        self::assertFalse($classGenerator->isAbstract());
    }

    public function testExtendedClassAccessors()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setExtendedClass('ExtendedClass');
        self::assertEquals($classGenerator->getExtendedClass(), null);
    }

    public function testImplementedInterfacesAccessors()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setImplementedInterfaces(['Class1', 'Class2']);
        self::assertCount(0, $classGenerator->getImplementedInterfaces());
    }

    public function testPropertyAccessors()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addProperties([
            'propOne',
            new PropertyGenerator('propTwo')
        ]);

        $properties = $classGenerator->getProperties();
        self::assertCount(2, $properties);
        self::assertInstanceOf(PropertyGenerator::class, current($properties));

        $property = $classGenerator->getProperty('propTwo');
        self::assertInstanceOf(PropertyGenerator::class, $property);
        self::assertEquals($property->getName(), 'propTwo');

        // add a new property
        $classGenerator->addProperty('prop3');
        self::assertCount(3, $classGenerator->getProperties());
    }

    public function testSetPropertyAlreadyExistsThrowsException()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addProperty('prop3');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A property by name prop3 already exists in this class');
        $classGenerator->addProperty('prop3');
    }

    public function testSetPropertyNoArrayOrPropertyThrowsException()
    {
        $classGenerator = new TraitGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Zend\Code\Generator\TraitGenerator::addProperty expects string for name');
        $classGenerator->addProperty(true);
    }

    public function testMethodAccessors()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addMethods([
            'methodOne',
            new MethodGenerator('methodTwo')
        ]);

        $methods = $classGenerator->getMethods();
        self::assertCount(2, $methods);
        self::assertInstanceOf(MethodGenerator::class, current($methods));

        $method = $classGenerator->getMethod('methodOne');
        self::assertInstanceOf(MethodGenerator::class, $method);
        self::assertEquals($method->getName(), 'methodOne');

        // add a new property
        $classGenerator->addMethod('methodThree');
        self::assertCount(3, $classGenerator->getMethods());
    }

    public function testSetMethodNoMethodOrArrayThrowsException()
    {
        $classGenerator = new TraitGenerator();

        $this->expectException(ExceptionInterface::class);
        $this->expectExceptionMessage('Zend\Code\Generator\TraitGenerator::addMethod expects string for name');

        $classGenerator->addMethod(true);
    }

    public function testSetMethodNameAlreadyExistsThrowsException()
    {
        $methodA = new MethodGenerator();
        $methodA->setName('foo');
        $methodB = new MethodGenerator();
        $methodB->setName('foo');

        $classGenerator = new TraitGenerator();
        $classGenerator->addMethodFromGenerator($methodA);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A method by name foo already exists in this class.');

        $classGenerator->addMethodFromGenerator($methodB);
    }

    /**
     * @group ZF-7361
     */
    public function testHasMethod()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addMethod('methodOne');

        self::assertTrue($classGenerator->hasMethod('methodOne'));
    }

    public function testRemoveMethod()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addMethod('methodOne');
        self::assertTrue($classGenerator->hasMethod('methodOne'));

        $classGenerator->removeMethod('methodOne');
        self::assertFalse($classGenerator->hasMethod('methodOne'));
    }

    /**
     * @group ZF-7361
     */
    public function testHasProperty()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addProperty('propertyOne');

        self::assertTrue($classGenerator->hasProperty('propertyOne'));
    }

    public function testToString()
    {
        $classGenerator = TraitGenerator::fromArray([
            'name' => 'SampleClass',
            'properties' => ['foo',
                ['name' => 'bar'],
            ],
            'methods' => [
                ['name' => 'baz'],
            ],
        ]);

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
        self::assertEquals($expectedOutput, $output, $output);
    }

    /**
     * @group ZF-7909
     */
    public function testClassFromReflectionThatImplementsInterfaces()
    {
        $reflClass = new ClassReflection(TestAsset\ClassWithInterface::class);

        $classGenerator = TraitGenerator::fromReflection($reflClass);
        $classGenerator->setSourceDirty(true);

        $code = $classGenerator->generate();

        $expectedClassDef = 'trait ClassWithInterface';
        self::assertContains($expectedClassDef, $code);
    }

    /**
     * @group ZF-7909
     */
    public function testClassFromReflectionDiscardParentImplementedInterfaces()
    {
        $reflClass = new ClassReflection(TestAsset\NewClassWithInterface::class);

        $classGenerator = TraitGenerator::fromReflection($reflClass);
        $classGenerator->setSourceDirty(true);

        $code = $classGenerator->generate();

        $expectedClassDef = 'trait NewClassWithInterface';
        self::assertContains($expectedClassDef, $code);
    }

    /**
     * @group 4988
     */
    public function testNonNamespaceClassReturnsAllMethods()
    {
        require_once __DIR__ . '/../TestAsset/NonNamespaceClass.php';

        $reflClass = new ClassReflection('ZendTest_Code_NsTest_BarClass');
        $classGenerator = TraitGenerator::fromReflection($reflClass);
        self::assertCount(1, $classGenerator->getMethods());
    }

    /**
     * @group ZF-9602
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
        self::assertEquals($expected, $classGeneratorClass->generate());
    }

    /**
     * @group ZF-9602
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
        self::assertEquals($expected, $classGeneratorClass->generate());
    }

    /**
     * @group namespace
     */
    public function testCodeGenerationShouldTakeIntoAccountNamespacesFromReflection()
    {
        $reflClass = new ClassReflection(TestAsset\ClassWithNamespace::class);
        $classGenerator = TraitGenerator::fromReflection($reflClass);
        self::assertEquals('ZendTest\Code\Generator\TestAsset', $classGenerator->getNamespaceName());
        self::assertEquals('ClassWithNamespace', $classGenerator->getName());
        $expected = <<<CODE
namespace ZendTest\Code\Generator\\TestAsset;

trait ClassWithNamespace
{


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
        $classGeneratorClass = new TraitGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        self::assertEquals('My\Namespaced', $classGeneratorClass->getNamespaceName());
    }

    /**
     * @group namespace
     */
    public function testPassingANamespacedClassnameShouldGenerateANamespaceDeclaration()
    {
        $classGeneratorClass = new TraitGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        $received = $classGeneratorClass->generate();
        self::assertContains('namespace My\Namespaced;', $received, $received);
    }

    /**
     * @group namespace
     */
    public function testPassingANamespacedClassnameShouldGenerateAClassnameWithoutItsNamespace()
    {
        $classGeneratorClass = new TraitGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        $received = $classGeneratorClass->generate();
        self::assertContains('trait FunClass', $received, $received);
    }

    /**
     * @group ZF2-151
     */
    public function testAddUses()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setName('My\Class');
        $classGenerator->addUse('My\First\Use\Class');
        $classGenerator->addUse('My\Second\Use\Class', 'MyAlias');
        $generated = $classGenerator->generate();

        self::assertContains('use My\First\Use\Class;', $generated);
        self::assertContains('use My\Second\Use\Class as MyAlias;', $generated);
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

        self::assertCount(1, $classGenerator->getUses());

        self::assertContains('use My\First\Use\Class;', $generated);
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

        self::assertCount(1, $classGenerator->getUses());

        self::assertContains('use My\First\Use\Class as MyAlias;', $generated);
    }

    public function testCreateFromArrayWithDocBlockFromArray()
    {
        $classGenerator = TraitGenerator::fromArray([
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
        $classGenerator = TraitGenerator::fromArray([
            'name' => 'SampleClass',
            'docblock' => new DocBlockGenerator('foo'),
        ]);

        $docBlock = $classGenerator->getDocBlock();
        self::assertInstanceOf(DocBlockGenerator::class, $docBlock);
    }

    public function testExtendedClassProperies()
    {
        $reflClass = new ClassReflection(TestAsset\ExtendedClassWithProperties::class);
        $classGenerator = TraitGenerator::fromReflection($reflClass);
        $code = $classGenerator->generate();
        self::assertContains('publicExtendedClassProperty', $code);
        self::assertContains('protectedExtendedClassProperty', $code);
        self::assertContains('privateExtendedClassProperty', $code);
        self::assertNotContains('publicClassProperty', $code);
        self::assertNotContains('protectedClassProperty', $code);
        self::assertNotContains('privateClassProperty', $code);
    }

    public function testHasMethodInsensitive()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addMethod('methodOne');

        self::assertTrue($classGenerator->hasMethod('methodOne'));
        self::assertTrue($classGenerator->hasMethod('MethoDonE'));
    }

    public function testRemoveMethodInsensitive()
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addMethod('methodOne');

        $classGenerator->removeMethod('METHODONe');
        self::assertFalse($classGenerator->hasMethod('methodOne'));
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
        self::assertEquals($expected, $output);
    }
}
