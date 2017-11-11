<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Generator;

use DateTime;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\Exception\ExceptionInterface;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\GeneratorInterface;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\NameInformation;
use Zend\Code\Reflection\ClassReflection;

use function current;
use function fclose;
use function fopen;
use function key;

/**
 * @group Zend_Code_Generator
 * @group Zend_Code_Generator_Php
 */
class ClassGeneratorTest extends TestCase
{
    public function testConstruction()
    {
        $class = new ClassGenerator();
        self::assertInstanceOf(ClassGenerator::class, $class);
    }

    public function testNameAccessors()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('TestClass');
        self::assertEquals('TestClass', $classGenerator->getName());
    }

    public function testClassDocBlockAccessors()
    {
        $docBlockGenerator = new DocBlockGenerator();
        $classGenerator = new ClassGenerator();
        $classGenerator->setDocBlock($docBlockGenerator);
        self::assertSame($docBlockGenerator, $classGenerator->getDocBlock());
    }

    public function testAbstractAccessors()
    {
        $classGenerator = new ClassGenerator();
        self::assertFalse($classGenerator->isAbstract());
        $classGenerator->setAbstract(true);
        self::assertTrue($classGenerator->isAbstract());
    }

    public function testExtendedClassAccessors()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setExtendedClass('ExtendedClass');
        self::assertEquals('ExtendedClass', $classGenerator->getExtendedClass());
    }

    public function testHasExtendedClass()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setExtendedClass('ExtendedClass');

        self::assertTrue($classGenerator->hasExtentedClass());
    }

    public function testRemoveExtendedClass()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setExtendedClass('ExtendedClass');
        self::assertTrue($classGenerator->hasExtentedClass());

        $classGenerator->removeExtentedClass();
        self::assertFalse($classGenerator->hasExtentedClass());
    }

    public function testImplementedInterfacesAccessors()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setImplementedInterfaces(['Class1', 'Class2']);
        self::assertEquals(['Class1', 'Class2'], $classGenerator->getImplementedInterfaces());
    }

    public function testHasImplementedInterface()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setImplementedInterfaces(['Class1', 'Class2']);

        self::assertTrue($classGenerator->hasImplementedInterface('Class1'));
    }

    public function testRemoveImplementedInterface()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setImplementedInterfaces(['Class1', 'Class2']);

        self::assertTrue($classGenerator->hasImplementedInterface('Class1'));

        $classGenerator->removeImplementedInterface('Class1');
        self::assertFalse($classGenerator->hasImplementedInterface('Class1'));
        self::assertTrue($classGenerator->hasImplementedInterface('Class2'));
    }

    public function testPropertyAccessors()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addProperties([
            'propOne',
            new PropertyGenerator('propTwo'),
        ]);

        $properties = $classGenerator->getProperties();
        self::assertCount(2, $properties);
        self::assertInstanceOf(PropertyGenerator::class, current($properties));

        $property = $classGenerator->getProperty('propTwo');
        self::assertInstanceOf(PropertyGenerator::class, $property);
        self::assertEquals('propTwo', $property->getName());

        // add a new property
        $classGenerator->addProperty('prop3');
        self::assertCount(3, $classGenerator->getProperties());
    }

    public function testSetPropertyAlreadyExistsThrowsException()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addProperty('prop3');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A property by name prop3 already exists in this class');
        $classGenerator->addProperty('prop3');
    }

    public function testSetPropertyNoArrayOrPropertyThrowsException()
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Zend\Code\Generator\ClassGenerator::addProperty expects string for name');
        $classGenerator->addProperty(true);
    }

    public function testMethodAccessors()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addMethods([
            'methodOne',
            new MethodGenerator('methodTwo'),
        ]);

        $methods = $classGenerator->getMethods();
        self::assertCount(2, $methods);
        self::assertInstanceOf(MethodGenerator::class, current($methods));

        $method = $classGenerator->getMethod('methodOne');
        self::assertInstanceOf(MethodGenerator::class, $method);
        self::assertEquals('methodOne', $method->getName());

        // add a new property
        $classGenerator->addMethod('methodThree');
        self::assertCount(3, $classGenerator->getMethods());
    }

    public function testSetMethodNoMethodOrArrayThrowsException()
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(ExceptionInterface::class);
        $this->expectExceptionMessage('Zend\Code\Generator\ClassGenerator::addMethod expects string for name');

        $classGenerator->addMethod(true);
    }

    public function testSetMethodNameAlreadyExistsThrowsException()
    {
        $methodA = new MethodGenerator();
        $methodA->setName('foo');
        $methodB = new MethodGenerator();
        $methodB->setName('foo');

        $classGenerator = new ClassGenerator();
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
        $classGenerator = new ClassGenerator();
        $classGenerator->addMethod('methodOne');

        self::assertTrue($classGenerator->hasMethod('methodOne'));
    }

    public function testRemoveMethod()
    {
        $classGenerator = new ClassGenerator();
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
        $classGenerator = new ClassGenerator();
        $classGenerator->addProperty('propertyOne');

        self::assertTrue($classGenerator->hasProperty('propertyOne'));
    }

    public function testRemoveProperty()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addProperty('propertyOne');
        self::assertTrue($classGenerator->hasProperty('propertyOne'));

        $classGenerator->removeProperty('propertyOne');
        self::assertFalse($classGenerator->hasProperty('propertyOne'));
    }

    public function testToString()
    {
        $classGenerator = ClassGenerator::fromArray([
            'name' => 'SampleClass',
            'flags' => ClassGenerator::FLAG_ABSTRACT,
            'extendedClass' => 'ExtendedClassName',
            'implementedInterfaces' => ['Iterator', 'Traversable'],
            'properties' => [
                'foo',
                ['name' => 'bar'],
            ],
            'methods' => [
                ['name' => 'baz'],
            ],
        ]);

        $expectedOutput = <<<EOS
abstract class SampleClass extends ExtendedClassName implements Iterator, Traversable
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

        $classGenerator = ClassGenerator::fromReflection($reflClass);
        $classGenerator->setSourceDirty(true);

        $code = $classGenerator->generate();

        $expectedClassDef = 'class ClassWithInterface'
            . ' implements OneInterface'
            . ', TwoInterface';
        self::assertContains($expectedClassDef, $code);
    }

    /**
     * @group ZF-7909
     */
    public function testClassFromReflectionDiscardParentImplementedInterfaces()
    {
        $reflClass = new ClassReflection(TestAsset\NewClassWithInterface::class);

        $classGenerator = ClassGenerator::fromReflection($reflClass);
        $classGenerator->setSourceDirty(true);

        $code = $classGenerator->generate();

        $expectedClassDef = 'class NewClassWithInterface'
            . ' extends ClassWithInterface'
            . ' implements ThreeInterface';
        self::assertContains($expectedClassDef, $code);
    }

    /**
     * @group 4988
     */
    public function testNonNamespaceClassReturnsAllMethods()
    {
        require_once __DIR__ . '/../TestAsset/NonNamespaceClass.php';

        $reflClass = new ClassReflection('ZendTest_Code_NsTest_BarClass');
        $classGenerator = ClassGenerator::fromReflection($reflClass);
        self::assertCount(1, $classGenerator->getMethods());
    }

    /**
     * @group ZF-9602
     */
    public function testSetextendedclassShouldIgnoreEmptyClassnameOnGenerate()
    {
        $classGeneratorClass = new ClassGenerator();
        $classGeneratorClass
            ->setName('MyClass')
            ->setExtendedClass('');

        $expected = <<<CODE
class MyClass
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
        $classGeneratorClass = new ClassGenerator();
        $classGeneratorClass
            ->setName('MyClass')
            ->setExtendedClass('ParentClass');

        $expected = <<<CODE
class MyClass extends ParentClass
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
        $classGenerator = ClassGenerator::fromReflection($reflClass);
        self::assertEquals('ZendTest\Code\Generator\TestAsset', $classGenerator->getNamespaceName());
        self::assertEquals('ClassWithNamespace', $classGenerator->getName());
        $expected = <<<CODE
namespace ZendTest\Code\Generator\\TestAsset;

class ClassWithNamespace
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
        $classGeneratorClass = new ClassGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        self::assertEquals('My\Namespaced', $classGeneratorClass->getNamespaceName());
    }

    /**
     * @group namespace
     */
    public function testPassingANamespacedClassnameShouldGenerateANamespaceDeclaration()
    {
        $classGeneratorClass = new ClassGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        $received = $classGeneratorClass->generate();
        self::assertContains('namespace My\Namespaced;', $received, $received);
    }

    /**
     * @group namespace
     */
    public function testPassingANamespacedClassnameShouldGenerateAClassnameWithoutItsNamespace()
    {
        $classGeneratorClass = new ClassGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        $received = $classGeneratorClass->generate();
        self::assertContains('class FunClass', $received, $received);
    }

    public function testHasUse()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addUse('My\First\Use\Class');
        $classGenerator->addUse('My\Second\Use\Class', 'MyAlias');

        self::assertTrue($classGenerator->hasUse('My\First\Use\Class'));
        self::assertTrue($classGenerator->hasUse('My\Second\Use\Class'));
    }

    public function testRemoveUse()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addUse('My\First\Use\Class');
        $classGenerator->addUse('My\Second\Use\Class', 'MyAlias');

        self::assertTrue($classGenerator->hasUse('My\First\Use\Class'));
        self::assertTrue($classGenerator->hasUse('My\Second\Use\Class'));
        $classGenerator->removeUse('My\First\Use\Class');
        $classGenerator->removeUse('My\Second\Use\Class');
        self::assertFalse($classGenerator->hasUse('My\First\Use\Class'));
        self::assertFalse($classGenerator->hasUse('My\Second\Use\Class'));
    }

    public function testHasUseAlias()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addUse('My\First\Use\Class');
        $classGenerator->addUse('My\Second\Use\Class', 'MyAlias');
        self::assertFalse($classGenerator->hasUseAlias('My\First\Use\Class'));
        self::assertTrue($classGenerator->hasUseAlias('My\Second\Use\Class'));
    }

    public function testRemoveUseAlias()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addUse('My\First\Use\Class', 'MyAlias');
        self::assertTrue($classGenerator->hasUseAlias('My\First\Use\Class'));
        $classGenerator->removeUseAlias('My\First\Use\Class');
        self::assertFalse($classGenerator->hasUseAlias('My\First\Use\Class'));
    }

    /**
     * @group ZF2-151
     */
    public function testAddUses()
    {
        $classGenerator = new ClassGenerator();
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
        $classGenerator = new ClassGenerator();
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
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('My\Class');
        $classGenerator->addUse('My\First\Use\Class', 'MyAlias');
        $classGenerator->addUse('My\First\Use\Class', 'MyAlias');
        $generated = $classGenerator->generate();

        self::assertCount(1, $classGenerator->getUses());

        self::assertContains('use My\First\Use\Class as MyAlias;', $generated);
    }

    public function testCreateFromArrayWithDocBlockFromArray()
    {
        $classGenerator = ClassGenerator::fromArray([
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
        $classGenerator = ClassGenerator::fromArray([
            'name' => 'SampleClass',
            'docblock' => new DocBlockGenerator('foo'),
        ]);

        $docBlock = $classGenerator->getDocBlock();
        self::assertInstanceOf(DocBlockGenerator::class, $docBlock);
    }

    public function testExtendedClassProperies()
    {
        $reflClass = new ClassReflection(TestAsset\ExtendedClassWithProperties::class);
        $classGenerator = ClassGenerator::fromReflection($reflClass);
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
        $classGenerator = new ClassGenerator();
        $classGenerator->addMethod('methodOne');

        self::assertTrue($classGenerator->hasMethod('methodOne'));
        self::assertTrue($classGenerator->hasMethod('MethoDonE'));
    }

    public function testRemoveMethodInsensitive()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addMethod('methodOne');

        $classGenerator->removeMethod('METHODONe');
        self::assertFalse($classGenerator->hasMethod('methodOne'));
    }

    public function testGenerateClassAndAddMethod()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('MyClass');
        $classGenerator->addMethod('methodOne');

        $expected = <<<CODE
class MyClass
{

    public function methodOne()
    {
    }


}

CODE;

        $output = $classGenerator->generate();
        self::assertEquals($expected, $output);
    }

    /**
     * @group 6274
     */
    public function testCanAddConstant()
    {
        $classGenerator = new ClassGenerator();

        $classGenerator->setName('My\Class');
        $classGenerator->addConstant('x', 'value');

        self::assertTrue($classGenerator->hasConstant('x'));

        $constant = $classGenerator->getConstant('x');

        self::assertInstanceOf(PropertyGenerator::class, $constant);
        self::assertTrue($constant->isConst());
        self::assertEquals('value', $constant->getDefaultValue()->getValue());
    }

    /**
     * @group 6274
     */
    public function testCanAddConstantsWithArrayOfGenerators()
    {
        $classGenerator = new ClassGenerator();

        $classGenerator->addConstants([
            new PropertyGenerator('x', 'value1', PropertyGenerator::FLAG_CONSTANT),
            new PropertyGenerator('y', 'value2', PropertyGenerator::FLAG_CONSTANT),
        ]);

        self::assertCount(2, $classGenerator->getConstants());
        self::assertEquals('value1', $classGenerator->getConstant('x')->getDefaultValue()->getValue());
        self::assertEquals('value2', $classGenerator->getConstant('y')->getDefaultValue()->getValue());
    }

    /**
     * @group 6274
     */
    public function testCanAddConstantsWithArrayOfKeyValues()
    {
        $classGenerator = new ClassGenerator();

        $classGenerator->addConstants([
            ['name' => 'x', 'value' => 'value1'],
            ['name' => 'y', 'value' => 'value2'],
        ]);

        self::assertCount(2, $classGenerator->getConstants());
        self::assertEquals('value1', $classGenerator->getConstant('x')->getDefaultValue()->getValue());
        self::assertEquals('value2', $classGenerator->getConstant('y')->getDefaultValue()->getValue());
    }

    /**
     * @group 6274
     */
    public function testAddConstantThrowsExceptionWithInvalidName()
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $classGenerator->addConstant([], 'value1');
    }

    public function testAddConstantThrowsExceptionWithEmptyConstantName()
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $classGenerator->addConstant('', 'value');
    }

    public function testAddConstantAcceptsMixedScalars()
    {
        $classGenerator = new ClassGenerator();

        $classGenerator->addConstant('a', 'v');
        $classGenerator->addConstant('b', 123);
        $classGenerator->addConstant('c', 123.456);
        $classGenerator->addConstant('d', []);
        $classGenerator->addConstant('e', ['v1' => 'v2']);
        $classGenerator->addConstant('f', ['v1' => ['v2' => 'v3']]);
        $classGenerator->addConstant('g', null);

        self::assertEquals('v', $classGenerator->getConstant('a')->getDefaultValue()->getValue());
        self::assertEquals(123, $classGenerator->getConstant('b')->getDefaultValue()->getValue());
        self::assertEquals(123.456, $classGenerator->getConstant('c')->getDefaultValue()->getValue());
        self::assertEquals([], $classGenerator->getConstant('d')->getDefaultValue()->getValue());
        self::assertEquals(['v1' => 'v2'], $classGenerator->getConstant('e')->getDefaultValue()->getValue());
        self::assertEquals(['v1' => ['v2' => 'v3']], $classGenerator->getConstant('f')->getDefaultValue()->getValue());
        self::assertEquals(null, $classGenerator->getConstant('g')->getDefaultValue()->getValue());
    }

    public function testAddConstantRejectsObjectConstantValue()
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $classGenerator->addConstant('a', new \stdClass());
    }

    public function testAddConstantRejectsResourceConstantValue()
    {
        $classGenerator = new ClassGenerator();

        $resource = fopen('php://memory', 'r');

        try {
            $classGenerator->addConstant('a', $resource);

            $this->fail('Not supposed to be reached');
        } catch (InvalidArgumentException $e) {
            self::assertEmpty($classGenerator->getConstants());
        } finally {
            fclose($resource);
        }
    }

    public function testAddConstantRejectsArrayWithInvalidNestedValue()
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $classGenerator->addConstant('a', [new \stdClass()]);
    }

    /**
     * @group 6274
     */
    public function testAddConstantThrowsExceptionOnDuplicate()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addConstant('x', 'value1');

        $this->expectException('InvalidArgumentException');
        $classGenerator->addConstant('x', 'value1');
    }

    public function testRemoveConstant()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addConstant('constantOne', 'foo');
        self::assertTrue($classGenerator->hasConstant('constantOne'));

        $classGenerator->removeConstant('constantOne');
        self::assertFalse($classGenerator->hasConstant('constantOne'));
    }

    /**
     * @group 6274
     */
    public function testAddPropertyIsBackwardsCompatibleWithConstants()
    {
        $classGenerator = new ClassGenerator();

        $classGenerator->addProperty('x', 'value1', PropertyGenerator::FLAG_CONSTANT);

        self::assertEquals('value1', $classGenerator->getConstant('x')->getDefaultValue()->getValue());
    }

    /**
     * @group 6274
     */
    public function testAddPropertiesIsBackwardsCompatibleWithConstants()
    {
        $constants = [
            new PropertyGenerator('x', 'value1', PropertyGenerator::FLAG_CONSTANT),
            new PropertyGenerator('y', 'value2', PropertyGenerator::FLAG_CONSTANT),
        ];
        $classGenerator = new ClassGenerator();

        $classGenerator->addProperties($constants);

        self::assertCount(2, $classGenerator->getConstants());
        self::assertEquals('value1', $classGenerator->getConstant('x')->getDefaultValue()->getValue());
        self::assertEquals('value2', $classGenerator->getConstant('y')->getDefaultValue()->getValue());
    }

    /**
     * @group 6274
     */
    public function testConstantsAddedFromReflection()
    {
        $reflector      = new ClassReflection(TestAsset\TestClassWithManyProperties::class);
        $classGenerator = ClassGenerator::fromReflection($reflector);
        $constant       = $classGenerator->getConstant('FOO');

        self::assertEquals('foo', $constant->getDefaultValue()->getValue());
    }

    /**
     * @group 6274
     */
    public function testClassCanBeGeneratedWithConstantAndPropertyWithSameName()
    {
        $reflector      = new ClassReflection(TestAsset\TestSampleSingleClass::class);
        $classGenerator = ClassGenerator::fromReflection($reflector);

        $classGenerator->addProperty('fooProperty', true, PropertyGenerator::FLAG_PUBLIC);
        $classGenerator->addConstant('fooProperty', 'duplicate');

        $contents = <<<'CODE'
namespace ZendTest\Code\Generator\TestAsset;

/**
 * class docblock
 */
class TestSampleSingleClass
{

    const fooProperty = 'duplicate';

    public $fooProperty = true;

    /**
     * Enter description here...
     *
     * @return bool
     */
    public function someMethod()
    {
        /* test test */
    }

    /**
     * Enter description here...
     *
     * @return bool
     */
    protected function withParamsAndReturnType($mixed, array $array, ?callable $callable = null, ?int $int = 0) : bool
    {
        /* test test */
        return true;
    }


}

CODE;

        self::assertEquals($classGenerator->generate(), $contents);
    }

    /**
     * @group 6253
     */
    public function testHereDoc()
    {
        $reflector = new ClassReflection(TestAsset\TestClassWithHeredoc::class);
        $classGenerator = new ClassGenerator();
        $methods = $reflector->getMethods();
        $classGenerator->setName('OutputClass');

        foreach ($methods as $method) {
            $methodGenerator = MethodGenerator::fromReflection($method);

            $classGenerator->addMethodFromGenerator($methodGenerator);
        }

        $contents = <<< 'CODE'
class OutputClass
{

    public function someFunction()
    {
        $output = <<< END

                Fix it, fix it!
                Fix it, fix it!
                Fix it, fix it!
END;
    }


}

CODE;

        self::assertEquals($contents, $classGenerator->generate());
    }

    public function testCanAddTraitWithString()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addTrait('myTrait');
        self::assertTrue($classGenerator->hasTrait('myTrait'));
    }

    public function testCanAddTraitWithArray()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addTrait(['traitName' => 'myTrait']);
        self::assertTrue($classGenerator->hasTrait('myTrait'));
    }

    public function testCanRemoveTrait()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addTrait(['traitName' => 'myTrait']);
        self::assertTrue($classGenerator->hasTrait('myTrait'));
        $classGenerator->removeTrait('myTrait');
        self::assertFalse($classGenerator->hasTrait('myTrait'));
    }

    public function testCanGetTraitsMethod()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addTraits(['myTrait', 'hisTrait']);

        $traits = $classGenerator->getTraits();
        self::assertContains('myTrait', $traits);
        self::assertContains('hisTrait', $traits);
    }

    public function testCanAddTraitAliasWithString()
    {
        $classGenerator = new ClassGenerator();

        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitAlias('myTrait::method', 'useMe', ReflectionMethod::IS_PRIVATE);

        $aliases = $classGenerator->getTraitAliases();
        self::assertArrayHasKey('myTrait::method', $aliases);
        self::assertEquals('useMe', $aliases['myTrait::method']['alias']);
        self::assertEquals(ReflectionMethod::IS_PRIVATE, $aliases['myTrait::method']['visibility']);
    }

    public function testCanAddTraitAliasWithArray()
    {
        $classGenerator = new ClassGenerator();

        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitAlias([
            'traitName' => 'myTrait',
            'method'    => 'method',
        ], 'useMe', ReflectionMethod::IS_PRIVATE);

        $aliases = $classGenerator->getTraitAliases();
        self::assertArrayHasKey('myTrait::method', $aliases);
        self::assertEquals('useMe', $aliases['myTrait::method']['alias']);
        self::assertEquals(ReflectionMethod::IS_PRIVATE, $aliases['myTrait::method']['visibility']);
    }

    public function testAddTraitAliasExceptionInvalidMethodFormat()
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Format: $method must be in the format of trait::method');

        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitAlias('method', 'useMe');
    }

    public function testAddTraitAliasExceptionInvalidMethodTraitDoesNotExist()
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid trait: Trait does not exists on this class');

        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitAlias('unknown::method', 'useMe');
    }

    public function testAddTraitAliasExceptionMethodAlreadyExists()
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Alias: Method name already exists on this class.');

        $classGenerator->addMethod('methodOne');
        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitAlias('myTrait::method', 'methodOne');
    }

    public function testAddTraitAliasExceptionInvalidVisibilityValue()
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid Type: $visibility must of ReflectionMethod::IS_PUBLIC,'
            . ' ReflectionMethod::IS_PRIVATE or ReflectionMethod::IS_PROTECTED'
        );

        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitAlias('myTrait::method', 'methodOne', 'public');
    }

    public function testAddTraitAliasExceptionInvalidAliasArgument()
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Alias: $alias must be a string or array.');

        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitAlias('myTrait::method', new ClassGenerator(), 'public');
    }

    public function testCanAddTraitOverride()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addTraits(['myTrait', 'histTrait']);
        $classGenerator->addTraitOverride('myTrait::foo', 'hisTrait');

        $overrides = $classGenerator->getTraitOverrides();
        self::assertCount(1, $overrides);
        self::assertEquals('myTrait::foo', key($overrides));
        self::assertEquals('hisTrait', $overrides['myTrait::foo'][0]);
    }

    public function testCanAddMultipleTraitOverrides()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addTraits(['myTrait', 'histTrait', 'thatTrait']);
        $classGenerator->addTraitOverride('myTrait::foo', ['hisTrait', 'thatTrait']);

        $overrides = $classGenerator->getTraitOverrides();
        self::assertCount(2, $overrides['myTrait::foo']);
        self::assertEquals('thatTrait', $overrides['myTrait::foo'][1]);
    }

    public function testAddTraitOverrideExceptionInvalidMethodFormat()
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Format: $method must be in the format of trait::method');

        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitOverride('method', 'useMe');
    }

    public function testAddTraitOverrideExceptionInvalidMethodTraitDoesNotExist()
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid trait: Trait does not exists on this class');

        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitOverride('unknown::method', 'useMe');
    }

    public function testAddTraitOverrideExceptionInvalidTraitName()
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required argument "traitName" for $method');

        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitOverride(['method' => 'foo'], 'test');
    }

    public function testAddTraitOverrideExceptionInvalidTraitToReplaceArgument()
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Argument: $traitToReplace must be a string or array of strings');

        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitOverride('myTrait::method', ['methodOne', 4]);
    }

    public function testAddTraitOverrideExceptionInvalidMethodArgInArray()
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required argument "method" for $method');

        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitOverride(['traitName' => 'myTrait'], 'test');
    }

    public function testCanRemoveTraitOverride()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addTraits(['myTrait', 'histTrait', 'thatTrait']);
        $classGenerator->addTraitOverride('myTrait::foo', ['hisTrait', 'thatTrait']);

        $overrides = $classGenerator->getTraitOverrides();
        self::assertCount(2, $overrides['myTrait::foo']);

        $classGenerator->removeTraitOverride('myTrait::foo', 'hisTrait');
        $overrides = $classGenerator->getTraitOverrides();

        self::assertCount(1, $overrides['myTrait::foo']);
        self::assertEquals('thatTrait', $overrides['myTrait::foo'][1]);
    }

    public function testCanRemoveAllTraitOverrides()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addTraits(['myTrait', 'histTrait', 'thatTrait']);
        $classGenerator->addTraitOverride('myTrait::foo', ['hisTrait', 'thatTrait']);

        $overrides = $classGenerator->getTraitOverrides();
        self::assertCount(2, $overrides['myTrait::foo']);

        $classGenerator->removeTraitOverride('myTrait::foo');
        $overrides = $classGenerator->getTraitOverrides();

        self::assertCount(0, $overrides);
    }

    /**
     * @group generate
     */
    public function testUseTraitGeneration()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('myClass');
        $classGenerator->addTrait('myTrait');
        $classGenerator->addTrait('hisTrait');
        $classGenerator->addTrait('thatTrait');

        $output = <<<'CODE'
class myClass
{

    use myTrait, hisTrait, thatTrait;


}

CODE;
        self::assertEquals($classGenerator->generate(), $output);
    }

    /**
     * @group generate
     */
    public function testTraitGenerationWithAliasesAndOverrides()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('myClass');
        $classGenerator->addTrait('myTrait');
        $classGenerator->addTrait('hisTrait');
        $classGenerator->addTrait('thatTrait');
        $classGenerator->addTraitAlias('hisTrait::foo', 'test', ReflectionMethod::IS_PUBLIC);
        $classGenerator->addTraitOverride('myTrait::bar', ['hisTrait', 'thatTrait']);

        $output = <<<'CODE'
class myClass
{

    use myTrait, hisTrait, thatTrait {
        hisTrait::foo as public test;
        myTrait::bar insteadof hisTrait;
        myTrait::bar insteadof thatTrait;

    }


}

CODE;
        self::assertEquals($classGenerator->generate(), $output);
    }

    public function testGenerateWithFinalFlag()
    {
        $classGenerator = ClassGenerator::fromArray([
            'name' => 'SomeClass',
            'flags' => ClassGenerator::FLAG_FINAL,
        ]);

        $expectedOutput = <<<EOS
final class SomeClass
{


}

EOS;

        $output = $classGenerator->generate();
        self::assertEquals($expectedOutput, $output, $output);
    }

    public function testCorrectExtendNames()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('ClassName');
        $classGenerator->setNamespaceName('SomeNamespace');
        $classGenerator->addUse(NameInformation::class);
        $classGenerator->setExtendedClass(NameInformation::class);
        self::assertContains('class ClassName extends NameInformation', $classGenerator->generate());
    }

    /**
     * @group 75
     */
    public function testCorrectlyExtendsFullyQualifiedParentClass()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('ClassName');
        $classGenerator->setNamespaceName('SomeNamespace');
        $classGenerator->setExtendedClass('DateTime');
        self::assertContains('class ClassName extends \DateTime', $classGenerator->generate());
    }

    /**
     * @group 75
     */
    public function testCorrectlyExtendsRelativeParentClass()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('ClassName');
        $classGenerator->setExtendedClass('DateTime');
        self::assertContains('class ClassName extends DateTime', $classGenerator->generate());
    }

    /**
     * @group 75
     */
    public function testCorrectExtendNamesFromGlobalNamespace()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('ClassName');
        $classGenerator->setNamespaceName('SomeNamespace');
        $classGenerator->setExtendedClass(DateTime::class);
        self::assertContains('class ClassName extends \DateTime', $classGenerator->generate());

        $classGenerator = new ClassGenerator();
        $classGenerator->setName('ClassName');
        $classGenerator->setExtendedClass(DateTime::class);
        self::assertContains('class ClassName extends DateTime', $classGenerator->generate());
    }

    public function testCorrectlyExtendsProvidedAliasIfUseAliasExists()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('ClassName');
        $classGenerator->setNamespaceName('SomeNamespace');
        $classGenerator->addUse('Foo\\Bar', 'BarAlias');
        $classGenerator->setExtendedClass('BarAlias');
        $generated = $classGenerator->generate();
        self::assertContains('class ClassName extends BarAlias', $generated);
    }

    public function testCorrectlyExtendsProvidedNamespaceAliasIfUseAliasExistsForNamespace()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('ClassName');
        $classGenerator->setNamespaceName('SomeNamespace');
        $classGenerator->addUse('Foo\\Bar', 'BarAlias');
        $classGenerator->setExtendedClass('BarAlias\\FooBar');
        $generated = $classGenerator->generate();
        self::assertContains('class ClassName extends BarAlias\\FooBar', $generated);
    }

    public function testCorrectlyExtendsAliasOfProvidedFQCNIfUseAliasExists()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('ClassName');
        $classGenerator->setNamespaceName('SomeNamespace');
        $classGenerator->addUse('Foo\\Bar', 'BarAlias');
        $classGenerator->setExtendedClass('Foo\\Bar');
        $generated = $classGenerator->generate();
        self::assertContains('class ClassName extends BarAlias', $generated);
    }

    public function testCorrectlyExtendsWithNamespaceAliasOfProvidedFQCNIfUseAliasExistsForNamespace()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('ClassName');
        $classGenerator->setNamespaceName('SomeNamespace');
        $classGenerator->addUse('Foo\\Bar', 'BarAlias');
        $classGenerator->setExtendedClass('Foo\\Bar\\FooBar');
        $generated = $classGenerator->generate();
        self::assertContains('class ClassName extends BarAlias\\FooBar', $generated);
    }

    public function testCorrectImplementNames()
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('ClassName');
        $classGenerator->setNamespaceName('SomeNamespace');
        $classGenerator->addUse(GeneratorInterface::class);
        $classGenerator->setImplementedInterfaces([
           'SomeNamespace\ClassInterface',
           GeneratorInterface::class,
           'Iteratable',
        ]);

        $expected = 'class ClassName implements ClassInterface, GeneratorInterface, \Iteratable';
        self::assertContains($expected, $classGenerator->generate());
    }
}
