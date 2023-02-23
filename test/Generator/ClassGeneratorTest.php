<?php

namespace LaminasTest\Code\Generator;

use DateTime;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\Exception\ExceptionInterface;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\GeneratorInterface;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PromotedParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Reflection\ClassReflection;
use LaminasTest\Code\Generator\TestAsset\ClassWithDnfTypes;
use LaminasTest\Code\Generator\TestAsset\ClassWithPromotedParameter;
use LaminasTest\Code\Generator\TestAsset\ReadonlyClassWithPromotedParameter;
use LaminasTest\Code\TestAsset\FooClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Serializable;
use stdClass;
use Throwable;

use function current;
use function fclose;
use function fopen;
use function key;

#[Group('Laminas_Code_Generator')]
#[Group('Laminas_Code_Generator_Php')]
class ClassGeneratorTest extends TestCase
{
    public function testConstruction(): void
    {
        $class = new ClassGenerator();
        self::assertInstanceOf(ClassGenerator::class, $class);
    }

    public function testNameAccessors(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('TestClass');
        self::assertSame('TestClass', $classGenerator->getName());
    }

    public function testClassDocBlockAccessors(): void
    {
        $docBlockGenerator = new DocBlockGenerator();
        $classGenerator    = new ClassGenerator();
        $classGenerator->setDocBlock($docBlockGenerator);
        self::assertSame($docBlockGenerator, $classGenerator->getDocBlock());
    }

    public function testAbstractAccessors(): void
    {
        $classGenerator = new ClassGenerator();
        self::assertFalse($classGenerator->isAbstract());
        $classGenerator->setAbstract(true);
        self::assertTrue($classGenerator->isAbstract());
    }

    public function testExtendedClassAccessors(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setExtendedClass('ExtendedClass');
        self::assertSame('ExtendedClass', $classGenerator->getExtendedClass());
    }

    public function testHasExtendedClass(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setExtendedClass('ExtendedClass');

        self::assertTrue($classGenerator->hasExtentedClass());
    }

    public function testRemoveExtendedClass(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setExtendedClass('ExtendedClass');
        self::assertTrue($classGenerator->hasExtentedClass());

        $classGenerator->removeExtentedClass();
        self::assertFalse($classGenerator->hasExtentedClass());
    }

    public function testImplementedInterfacesAccessors(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setImplementedInterfaces(['Class1', 'Class2']);
        self::assertSame(['Class1', 'Class2'], $classGenerator->getImplementedInterfaces());
    }

    public function testHasImplementedInterface(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setImplementedInterfaces([Throwable::class, Serializable::class]);

        self::assertTrue($classGenerator->hasImplementedInterface(Throwable::class));
    }

    public function testRemoveImplementedInterface(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setImplementedInterfaces([Throwable::class, Serializable::class]);

        self::assertTrue($classGenerator->hasImplementedInterface(Throwable::class));

        $classGenerator->removeImplementedInterface(Throwable::class);
        self::assertFalse($classGenerator->hasImplementedInterface(Throwable::class));
        self::assertTrue($classGenerator->hasImplementedInterface(Serializable::class));
    }

    public function testPropertyAccessors(): void
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
        self::assertSame('propTwo', $property->getName());

        // add a new property
        $classGenerator->addProperty('prop3');
        self::assertCount(3, $classGenerator->getProperties());
    }

    public function testSetPropertyAlreadyExistsThrowsException(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addProperty('prop3');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A property by name prop3 already exists in this class');
        $classGenerator->addProperty('prop3');
    }

    public function testSetPropertyNoArrayOrPropertyThrowsException(): void
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Laminas\Code\Generator\ClassGenerator::addProperty expects string for name');
        $classGenerator->addProperty(true);
    }

    public function testMethodAccessors(): void
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
        self::assertSame('methodOne', $method->getName());

        // add a new property
        $classGenerator->addMethod('methodThree');
        self::assertCount(3, $classGenerator->getMethods());
    }

    public function testSetMethodNoMethodOrArrayThrowsException(): void
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(ExceptionInterface::class);
        $this->expectExceptionMessage('Laminas\Code\Generator\ClassGenerator::addMethod expects string for name');

        $classGenerator->addMethod(true);
    }

    public function testSetMethodNameAlreadyExistsThrowsException(): void
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

    #[Group('Laminas-7361')]
    public function testHasMethod(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addMethod('methodOne');

        self::assertTrue($classGenerator->hasMethod('methodOne'));
    }

    public function testRemoveMethod(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addMethod('methodOne');
        self::assertTrue($classGenerator->hasMethod('methodOne'));

        $classGenerator->removeMethod('methodOne');
        self::assertFalse($classGenerator->hasMethod('methodOne'));
    }

    #[Group('Laminas-7361')]
    public function testHasProperty(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addProperty('propertyOne');

        self::assertTrue($classGenerator->hasProperty('propertyOne'));
    }

    public function testRemoveProperty(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addProperty('propertyOne');
        self::assertTrue($classGenerator->hasProperty('propertyOne'));

        $classGenerator->removeProperty('propertyOne');
        self::assertFalse($classGenerator->hasProperty('propertyOne'));
    }

    public function testToString(): void
    {
        $classGenerator = ClassGenerator::fromArray([
            'name'                  => 'SampleClass',
            'flags'                 => ClassGenerator::FLAG_ABSTRACT,
            'extendedClass'         => 'ExtendedClassName',
            'implementedInterfaces' => ['Iterator', 'Traversable'],
            'properties'            => [
                'foo',
                ['name' => 'bar'],
            ],
            'methods'               => [
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
        self::assertSame($expectedOutput, $output, $output);
    }

    #[Group('Laminas-7909')]
    public function testClassFromReflectionThatImplementsInterfaces(): void
    {
        $reflClass = new ClassReflection(TestAsset\ClassWithInterface::class);

        $classGenerator = ClassGenerator::fromReflection($reflClass);
        $classGenerator->setSourceDirty(true);

        $code = $classGenerator->generate();

        $expectedClassDef = 'class ClassWithInterface'
            . ' implements OneInterface'
            . ', TwoInterface';
        self::assertStringContainsString($expectedClassDef, $code);
    }

    #[Group('Laminas-7909')]
    public function testClassFromReflectionDiscardParentImplementedInterfaces(): void
    {
        $reflClass = new ClassReflection(TestAsset\NewClassWithInterface::class);

        $classGenerator = ClassGenerator::fromReflection($reflClass);
        $classGenerator->setSourceDirty(true);

        $code = $classGenerator->generate();

        $expectedClassDef = 'class NewClassWithInterface'
            . ' extends ClassWithInterface'
            . ' implements ThreeInterface';
        self::assertStringContainsString($expectedClassDef, $code);
    }

    #[Group('4988')]
    public function testNonNamespaceClassReturnsAllMethods(): void
    {
        require_once __DIR__ . '/../TestAsset/NonNamespaceClass.php';

        $reflClass      = new ClassReflection('LaminasTest_Code_NsTest_BarClass');
        $classGenerator = ClassGenerator::fromReflection($reflClass);
        self::assertCount(1, $classGenerator->getMethods());
    }

    #[Group('Laminas-9602')]
    public function testSetextendedclassShouldIgnoreEmptyClassnameOnGenerate(): void
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
        self::assertSame($expected, $classGeneratorClass->generate());
    }

    #[Group('Laminas-9602')]
    public function testSetextendedclassShouldNotIgnoreNonEmptyClassnameOnGenerate(): void
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
        self::assertSame($expected, $classGeneratorClass->generate());
    }

    #[Group('namespace')]
    public function testCodeGenerationShouldTakeIntoAccountNamespacesFromReflection(): void
    {
        $reflClass      = new ClassReflection(TestAsset\ClassWithNamespace::class);
        $classGenerator = ClassGenerator::fromReflection($reflClass);
        self::assertSame('LaminasTest\Code\Generator\TestAsset', $classGenerator->getNamespaceName());
        self::assertSame('ClassWithNamespace', $classGenerator->getName());
        $expected = <<<CODE
namespace LaminasTest\Code\Generator\\TestAsset;

class ClassWithNamespace
{
}

CODE;
        $received = $classGenerator->generate();
        self::assertSame($expected, $received, $received);
    }

    #[Group('namespace')]
    public function testSetNameShouldDetermineIfNamespaceSegmentIsPresent(): void
    {
        $classGeneratorClass = new ClassGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        self::assertSame('My\Namespaced', $classGeneratorClass->getNamespaceName());
    }

    #[Group('namespace')]
    public function testPassingANamespacedClassnameShouldGenerateANamespaceDeclaration(): void
    {
        $classGeneratorClass = new ClassGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        $received = $classGeneratorClass->generate();
        self::assertStringContainsString('namespace My\Namespaced;', $received, $received);
    }

    #[Group('namespace')]
    public function testPassingANamespacedClassnameShouldGenerateAClassnameWithoutItsNamespace(): void
    {
        $classGeneratorClass = new ClassGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        $received = $classGeneratorClass->generate();
        self::assertStringContainsString('class FunClass', $received, $received);
    }

    public function testHasUse(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addUse('My\First\Use\Class');
        $classGenerator->addUse('My\Second\Use\Class', 'MyAlias');

        self::assertTrue($classGenerator->hasUse('My\First\Use\Class'));
        self::assertTrue($classGenerator->hasUse('My\Second\Use\Class'));
    }

    public function testRemoveUse(): void
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

    public function testHasUseAlias(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addUse('My\First\Use\Class');
        $classGenerator->addUse('My\Second\Use\Class', 'MyAlias');
        self::assertFalse($classGenerator->hasUseAlias('My\First\Use\Class'));
        self::assertTrue($classGenerator->hasUseAlias('My\Second\Use\Class'));
    }

    public function testRemoveUseAlias(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addUse('My\First\Use\Class', 'MyAlias');
        self::assertTrue($classGenerator->hasUseAlias('My\First\Use\Class'));
        $classGenerator->removeUseAlias('My\First\Use\Class');
        self::assertFalse($classGenerator->hasUseAlias('My\First\Use\Class'));
    }

    #[Group('Laminas-151')]
    public function testAddUses(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('My\Class');
        $classGenerator->addUse('My\First\Use\Class');
        $classGenerator->addUse('My\Second\Use\Class', 'MyAlias');
        $generated = $classGenerator->generate();

        self::assertStringContainsString('use My\First\Use\Class;', $generated);
        self::assertStringContainsString('use My\Second\Use\Class as MyAlias;', $generated);
    }

    #[Group('4990')]
    public function testAddOneUseTwiceOnlyAddsOne(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('My\Class');
        $classGenerator->addUse('My\First\Use\Class');
        $classGenerator->addUse('My\First\Use\Class');
        $generated = $classGenerator->generate();

        self::assertCount(1, $classGenerator->getUses());

        self::assertStringContainsString('use My\First\Use\Class;', $generated);
    }

    #[Group('4990')]
    public function testAddOneUseWithAliasTwiceOnlyAddsOne(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('My\Class');
        $classGenerator->addUse('My\First\Use\Class', 'MyAlias');
        $classGenerator->addUse('My\First\Use\Class', 'MyAlias');
        $generated = $classGenerator->generate();

        self::assertCount(1, $classGenerator->getUses());

        self::assertStringContainsString('use My\First\Use\Class as MyAlias;', $generated);
    }

    public function testCreateFromArrayWithDocBlockFromArray(): void
    {
        $classGenerator = ClassGenerator::fromArray([
            'name'     => 'SampleClass',
            'docblock' => [
                'shortdescription' => 'foo',
            ],
        ]);

        $docBlock = $classGenerator->getDocBlock();
        self::assertInstanceOf(DocBlockGenerator::class, $docBlock);
    }

    public function testCreateFromArrayWithDocBlockInstance(): void
    {
        $classGenerator = ClassGenerator::fromArray([
            'name'     => 'SampleClass',
            'docblock' => new DocBlockGenerator('foo'),
        ]);

        $docBlock = $classGenerator->getDocBlock();
        self::assertInstanceOf(DocBlockGenerator::class, $docBlock);
    }

    public function testExtendedClassProperies(): void
    {
        $reflClass      = new ClassReflection(TestAsset\ExtendedClassWithProperties::class);
        $classGenerator = ClassGenerator::fromReflection($reflClass);
        $code           = $classGenerator->generate();
        self::assertStringContainsString('publicExtendedClassProperty', $code);
        self::assertStringContainsString('protectedExtendedClassProperty', $code);
        self::assertStringContainsString('privateExtendedClassProperty', $code);
        self::assertStringNotContainsString('publicClassProperty', $code);
        self::assertStringNotContainsString('protectedClassProperty', $code);
        self::assertStringNotContainsString('privateClassProperty', $code);
    }

    public function testHasMethodInsensitive(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addMethod('methodOne');

        self::assertTrue($classGenerator->hasMethod('methodOne'));
        self::assertTrue($classGenerator->hasMethod('MethoDonE'));
    }

    public function testRemoveMethodInsensitive(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addMethod('methodOne');

        $classGenerator->removeMethod('METHODONe');
        self::assertFalse($classGenerator->hasMethod('methodOne'));
    }

    public function testGenerateClassAndAddMethod(): void
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
        self::assertSame($expected, $output);
    }

    #[Group('6274')]
    public function testCanAddConstant(): void
    {
        $classGenerator = new ClassGenerator();

        $classGenerator->setName('My\Class');
        $classGenerator->addConstant('x', 'value');

        self::assertTrue($classGenerator->hasConstant('x'));

        $constant = $classGenerator->getConstant('x');

        self::assertInstanceOf(PropertyGenerator::class, $constant);
        self::assertTrue($constant->isConst());

        $defaultValue = $constant->getDefaultValue();
        self::assertNotNull($defaultValue);
        self::assertSame('value', $defaultValue->getValue());
    }

    #[Group('6274')]
    public function testCanAddConstantsWithArrayOfGenerators(): void
    {
        $classGenerator = new ClassGenerator();

        $classGenerator->addConstants([
            new PropertyGenerator('x', 'value1', PropertyGenerator::FLAG_CONSTANT),
            new PropertyGenerator('y', 'value2', PropertyGenerator::FLAG_CONSTANT),
        ]);

        self::assertCount(2, $classGenerator->getConstants());
        $valueX = $classGenerator->getConstant('x')->getDefaultValue();
        self::assertNotNull($valueX);
        self::assertSame('value1', $valueX->getValue());

        $valueY = $classGenerator->getConstant('y')->getDefaultValue();
        self::assertNotNull($valueY);
        self::assertSame('value2', $valueY->getValue());
    }

    #[Group('6274')]
    public function testCanAddConstantsWithArrayOfKeyValues(): void
    {
        $classGenerator = new ClassGenerator();

        $classGenerator->addConstants([
            ['name' => 'x', 'value' => 'value1'],
            ['name' => 'y', 'value' => 'value2'],
        ]);

        self::assertCount(2, $classGenerator->getConstants());

        $valueX = $classGenerator->getConstant('x')->getDefaultValue();
        self::assertNotNull($valueX);
        self::assertSame('value1', $valueX->getValue());

        $valueY = $classGenerator->getConstant('y')->getDefaultValue();
        self::assertNotNull($valueY);
        self::assertSame('value2', $valueY->getValue());
    }

    #[Group('6274')]
    public function testAddConstantThrowsExceptionWithInvalidName(): void
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $classGenerator->addConstant([], 'value1');
    }

    public function testAddConstantThrowsExceptionWithEmptyConstantName(): void
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $classGenerator->addConstant('', 'value');
    }

    public function testAddConstantAcceptsMixedScalars(): void
    {
        $classGenerator = new ClassGenerator();

        $classGenerator->addConstant('a', 'v');
        $classGenerator->addConstant('b', 123);
        $classGenerator->addConstant('c', 123.456);
        $classGenerator->addConstant('d', []);
        $classGenerator->addConstant('e', ['v1' => 'v2']);
        $classGenerator->addConstant('f', ['v1' => ['v2' => 'v3']]);
        $classGenerator->addConstant('g', null);

        $valueA = $classGenerator->getConstant('a')->getDefaultValue();
        self::assertNotNull($valueA);
        self::assertSame('v', $valueA->getValue());

        $valueB = $classGenerator->getConstant('b')->getDefaultValue();
        self::assertNotNull($valueB);
        self::assertSame(123, $valueB->getValue());

        $valueC = $classGenerator->getConstant('c')->getDefaultValue();
        self::assertNotNull($valueC);
        self::assertSame(123.456, $valueC->getValue());

        $valueD = $classGenerator->getConstant('d')->getDefaultValue();
        self::assertNotNull($valueD);
        self::assertSame([], $valueD->getValue());

        $valueE = $classGenerator->getConstant('e')->getDefaultValue();
        self::assertNotNull($valueE);
        self::assertSame(['v1' => 'v2'], $valueE->getValue());

        $valueF = $classGenerator->getConstant('f')->getDefaultValue();
        self::assertNotNull($valueF);
        self::assertSame(['v1' => ['v2' => 'v3']], $valueF->getValue());

        $valueG = $classGenerator->getConstant('g')->getDefaultValue();
        self::assertNotNull($valueG);
        self::assertSame(null, $valueG->getValue());
    }

    public function testAddConstantRejectsObjectConstantValue(): void
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $classGenerator->addConstant('a', new stdClass());
    }

    public function testAddConstantRejectsResourceConstantValue(): void
    {
        $classGenerator = new ClassGenerator();

        $resource = fopen('php://memory', 'r');

        try {
            $classGenerator->addConstant('a', $resource);

            $this->fail('Not supposed to be reached');
        } catch (InvalidArgumentException) {
            self::assertEmpty($classGenerator->getConstants());
        } finally {
            fclose($resource);
        }
    }

    public function testAddConstantRejectsArrayWithInvalidNestedValue(): void
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $classGenerator->addConstant('a', [new stdClass()]);
    }

    #[Group('6274')]
    public function testAddConstantThrowsExceptionOnDuplicate(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addConstant('x', 'value1');

        $this->expectException(InvalidArgumentException::class);
        $classGenerator->addConstant('x', 'value1');
    }

    public function testRemoveConstant(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addConstant('constantOne', 'foo');
        self::assertTrue($classGenerator->hasConstant('constantOne'));

        $classGenerator->removeConstant('constantOne');
        self::assertFalse($classGenerator->hasConstant('constantOne'));
    }

    #[Group('6274')]
    public function testAddPropertyIsBackwardsCompatibleWithConstants(): void
    {
        $classGenerator = new ClassGenerator();

        $classGenerator->addProperty('x', 'value1', PropertyGenerator::FLAG_CONSTANT);

        $valueX = $classGenerator->getConstant('x')->getDefaultValue();
        self::assertNotNull($valueX);
        self::assertSame('value1', $valueX->getValue());
    }

    #[Group('6274')]
    public function testAddPropertiesIsBackwardsCompatibleWithConstants(): void
    {
        $constants      = [
            new PropertyGenerator('x', 'value1', PropertyGenerator::FLAG_CONSTANT),
            new PropertyGenerator('y', 'value2', PropertyGenerator::FLAG_CONSTANT),
        ];
        $classGenerator = new ClassGenerator();

        $classGenerator->addProperties($constants);

        self::assertCount(2, $classGenerator->getConstants());
        $valueX = $classGenerator->getConstant('x')->getDefaultValue();
        self::assertNotNull($valueX);
        self::assertSame('value1', $valueX->getValue());

        $valueY = $classGenerator->getConstant('y')->getDefaultValue();
        self::assertNotNull($valueY);
        self::assertSame('value2', $valueY->getValue());
    }

    #[Group('6274')]
    public function testConstantsAddedFromReflection(): void
    {
        $reflector      = new ClassReflection(TestAsset\TestClassWithManyProperties::class);
        $classGenerator = ClassGenerator::fromReflection($reflector);
        $constant       = $classGenerator->getConstant('FOO');

        $constantValue = $constant->getDefaultValue();
        self::assertNotNull($constantValue);
        self::assertSame('foo', $constantValue->getValue());
    }

    #[Group('6274')]
    public function testClassCanBeGeneratedWithConstantAndPropertyWithSameName(): void
    {
        $reflector      = new ClassReflection(TestAsset\TestSampleSingleClass::class);
        $classGenerator = ClassGenerator::fromReflection($reflector);

        $classGenerator->addProperty('fooProperty', true, PropertyGenerator::FLAG_PUBLIC);
        $classGenerator->addConstant('fooProperty', 'duplicate');

        $contents = <<<'CODE'
namespace LaminasTest\Code\Generator\TestAsset;

/**
 * class docblock
 */
class TestSampleSingleClass
{
    public const fooProperty = 'duplicate';

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

        self::assertSame($contents, $classGenerator->generate());
    }

    #[Group('6253')]
    public function testHereDoc(): void
    {
        $reflector      = new ClassReflection(TestAsset\TestClassWithHeredoc::class);
        $classGenerator = new ClassGenerator();
        $methods        = $reflector->getMethods();
        $classGenerator->setName('OutputClass');

        foreach ($methods as $method) {
            $methodGenerator = MethodGenerator::fromReflection($method);

            $classGenerator->addMethodFromGenerator($methodGenerator);
        }

        $contents = <<<'CODE'
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

        self::assertSame($contents, $classGenerator->generate());
    }

    public function testCanAddTraitWithString(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addTrait('myTrait');
        self::assertTrue($classGenerator->hasTrait('myTrait'));
    }

    public function testCanAddTraitWithArray(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addTrait(['traitName' => 'myTrait']);
        self::assertTrue($classGenerator->hasTrait('myTrait'));
    }

    public function testCanRemoveTrait(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addTrait(['traitName' => 'myTrait']);
        self::assertTrue($classGenerator->hasTrait('myTrait'));
        $classGenerator->removeTrait('myTrait');
        self::assertFalse($classGenerator->hasTrait('myTrait'));
    }

    public function testCanGetTraitsMethod(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addTraits(['myTrait', 'hisTrait']);

        $traits = $classGenerator->getTraits();
        self::assertContains('myTrait', $traits);
        self::assertContains('hisTrait', $traits);
    }

    public function testCanAddTraitAliasWithString(): void
    {
        $classGenerator = new ClassGenerator();

        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitAlias('myTrait::method', 'useMe', ReflectionMethod::IS_PRIVATE);

        $aliases = $classGenerator->getTraitAliases();
        self::assertArrayHasKey('myTrait::method', $aliases);
        self::assertSame('useMe', $aliases['myTrait::method']['alias']);
        self::assertSame(ReflectionMethod::IS_PRIVATE, $aliases['myTrait::method']['visibility']);
    }

    public function testCanAddTraitAliasWithArray(): void
    {
        $classGenerator = new ClassGenerator();

        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitAlias([
            'traitName' => 'myTrait',
            'method'    => 'method',
        ], 'useMe', ReflectionMethod::IS_PRIVATE);

        $aliases = $classGenerator->getTraitAliases();
        self::assertArrayHasKey('myTrait::method', $aliases);
        self::assertSame('useMe', $aliases['myTrait::method']['alias']);
        self::assertSame(ReflectionMethod::IS_PRIVATE, $aliases['myTrait::method']['visibility']);
    }

    public function testAddTraitAliasExceptionInvalidMethodFormat(): void
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Format: $method must be in the format of trait::method');

        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitAlias('method', 'useMe');
    }

    public function testAddTraitAliasExceptionInvalidMethodTraitDoesNotExist(): void
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid trait: Trait does not exists on this class');

        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitAlias('unknown::method', 'useMe');
    }

    public function testAddTraitAliasExceptionMethodAlreadyExists(): void
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Alias: Method name already exists on this class.');

        $classGenerator->addMethod('methodOne');
        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitAlias('myTrait::method', 'methodOne');
    }

    public function testAddTraitAliasExceptionInvalidVisibilityValue(): void
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

    public function testAddTraitAliasExceptionInvalidAliasArgument(): void
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Alias: $alias must be a string or array.');

        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitAlias('myTrait::method', new ClassGenerator(), 'public');
    }

    public function testCanAddTraitOverride(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addTraits(['myTrait', 'histTrait']);
        $classGenerator->addTraitOverride('myTrait::foo', 'hisTrait');

        $overrides = $classGenerator->getTraitOverrides();
        self::assertCount(1, $overrides);
        self::assertSame('myTrait::foo', key($overrides));
        self::assertSame('hisTrait', $overrides['myTrait::foo'][0]);
    }

    public function testCanAddMultipleTraitOverrides(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addTraits(['myTrait', 'histTrait', 'thatTrait']);
        $classGenerator->addTraitOverride('myTrait::foo', ['hisTrait', 'thatTrait']);

        $overrides = $classGenerator->getTraitOverrides();
        self::assertCount(2, $overrides['myTrait::foo']);
        self::assertSame('thatTrait', $overrides['myTrait::foo'][1]);
    }

    public function testAddTraitOverrideExceptionInvalidMethodFormat(): void
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Format: $method must be in the format of trait::method');

        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitOverride('method', 'useMe');
    }

    public function testAddTraitOverrideExceptionInvalidMethodTraitDoesNotExist(): void
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid trait: Trait does not exists on this class');

        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitOverride('unknown::method', 'useMe');
    }

    public function testAddTraitOverrideExceptionInvalidTraitName(): void
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required argument "traitName" for $method');

        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitOverride(['method' => 'foo'], 'test');
    }

    public function testAddTraitOverrideExceptionInvalidTraitToReplaceArgument(): void
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Argument: $traitToReplace must be a string or array of strings');

        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitOverride('myTrait::method', ['methodOne', 4]);
    }

    public function testAddTraitOverrideExceptionInvalidMethodArgInArray(): void
    {
        $classGenerator = new ClassGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required argument "method" for $method');

        $classGenerator->addTrait('myTrait');
        $classGenerator->addTraitOverride(['traitName' => 'myTrait'], 'test');
    }

    public function testCanRemoveTraitOverride(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->addTraits(['myTrait', 'histTrait', 'thatTrait']);
        $classGenerator->addTraitOverride('myTrait::foo', ['hisTrait', 'thatTrait']);

        $overrides = $classGenerator->getTraitOverrides();
        self::assertCount(2, $overrides['myTrait::foo']);

        $classGenerator->removeTraitOverride('myTrait::foo', 'hisTrait');
        $overrides = $classGenerator->getTraitOverrides();

        self::assertCount(1, $overrides['myTrait::foo']);
        self::assertSame('thatTrait', $overrides['myTrait::foo'][1]);
    }

    public function testCanRemoveAllTraitOverrides(): void
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

    #[Group('generate')]
    public function testUseTraitGeneration(): void
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
        self::assertSame($output, $classGenerator->generate());
    }

    #[Group('generate')]
    public function testTraitGenerationWithAliasesAndOverrides(): void
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
        self::assertSame($output, $classGenerator->generate());
    }

    public function testGenerateWithFinalFlag(): void
    {
        $classGenerator = ClassGenerator::fromArray([
            'name'  => 'SomeClass',
            'flags' => ClassGenerator::FLAG_FINAL,
        ]);

        $expectedOutput = <<<EOS
final class SomeClass
{
}

EOS;

        $output = $classGenerator->generate();
        self::assertSame($expectedOutput, $output, $output);
    }

    public function testGenerateWithFinalReadonlyFlag(): void
    {
        $classGenerator = ClassGenerator::fromArray([
            'name'  => 'SomeClass',
            'flags' => ClassGenerator::FLAG_FINAL | ClassGenerator::FLAG_READONLY,
        ]);

        $expectedOutput = <<<EOS
final readonly class SomeClass
{
}

EOS;

        $output = $classGenerator->generate();
        self::assertSame($expectedOutput, $output, $output);
    }

    public function testCorrectExtendNames(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('ClassName');
        $classGenerator->setNamespaceName('SomeNamespace');
        $classGenerator->addUse(FooClass::class);
        $classGenerator->setExtendedClass(FooClass::class);
        self::assertStringContainsString('class ClassName extends FooClass', $classGenerator->generate());
    }

    #[Group('75')]
    public function testCorrectlyExtendsFullyQualifiedParentClass(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('ClassName');
        $classGenerator->setNamespaceName('SomeNamespace');
        $classGenerator->setExtendedClass(DateTime::class);
        self::assertStringContainsString('class ClassName extends \DateTime', $classGenerator->generate());
    }

    #[Group('75')]
    public function testCorrectlyExtendsRelativeParentClass(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('ClassName');
        $classGenerator->setExtendedClass(DateTime::class);
        self::assertStringContainsString('class ClassName extends DateTime', $classGenerator->generate());
    }

    #[Group('75')]
    public function testCorrectExtendNamesFromGlobalNamespace(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('ClassName');
        $classGenerator->setNamespaceName('SomeNamespace');
        $classGenerator->setExtendedClass(DateTime::class);
        self::assertStringContainsString('class ClassName extends \DateTime', $classGenerator->generate());

        $classGenerator = new ClassGenerator();
        $classGenerator->setName('ClassName');
        $classGenerator->setExtendedClass(DateTime::class);
        self::assertStringContainsString('class ClassName extends DateTime', $classGenerator->generate());
    }

    public function testCorrectlyExtendsProvidedAliasIfUseAliasExists(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('ClassName');
        $classGenerator->setNamespaceName('SomeNamespace');
        /** @psalm-var class-string $useAlias */
        $useAlias = 'BarAlias';
        $classGenerator->addUse('Foo\\Bar', $useAlias);
        $classGenerator->setExtendedClass($useAlias);
        $generated = $classGenerator->generate();
        self::assertStringContainsString('class ClassName extends BarAlias', $generated);
    }

    public function testCorrectlyExtendsProvidedNamespaceAliasIfUseAliasExistsForNamespace(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('ClassName');
        $classGenerator->setNamespaceName('SomeNamespace');
        $classGenerator->addUse('Foo\\Bar', 'BarAlias');
        /** @psalm-var class-string $extendedClass */
        $extendedClass = 'BarAlias\\FooBar';
        $classGenerator->setExtendedClass($extendedClass);
        $generated = $classGenerator->generate();
        self::assertStringContainsString('class ClassName extends BarAlias\\FooBar', $generated);
    }

    public function testCorrectlyExtendsAliasOfProvidedFQCNIfUseAliasExists(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('ClassName');
        $classGenerator->setNamespaceName('SomeNamespace');
        $classGenerator->addUse('Foo\\Bar', 'BarAlias');

        /** @psalm-var class-string $extendedClass */
        $extendedClass = 'Foo\\Bar';
        $classGenerator->setExtendedClass($extendedClass);
        $generated = $classGenerator->generate();
        self::assertStringContainsString('class ClassName extends BarAlias', $generated);
    }

    public function testCorrectlyExtendsWithNamespaceAliasOfProvidedFQCNIfUseAliasExistsForNamespace(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('ClassName');
        $classGenerator->setNamespaceName('SomeNamespace');
        $classGenerator->addUse('Foo\\Bar', 'BarAlias');

        /** @psalm-var class-string */
        $extendedClass = 'Foo\\Bar\\FooBar';
        $classGenerator->setExtendedClass($extendedClass);
        $generated = $classGenerator->generate();
        self::assertStringContainsString('class ClassName extends BarAlias\\FooBar', $generated);
    }

    public function testCorrectImplementNames(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('ClassName');
        $classGenerator->setNamespaceName('SomeNamespace');
        $classGenerator->addUse(GeneratorInterface::class);

        /** @psalm-var array<class-string> */
        $implementedInterfaces = [
            'SomeNamespace\ClassInterface',
            GeneratorInterface::class,
            'Iteratable',
        ];
        $classGenerator->setImplementedInterfaces($implementedInterfaces);

        $expected = 'class ClassName implements ClassInterface, GeneratorInterface, \Iteratable';
        self::assertStringContainsString($expected, $classGenerator->generate());
    }

    public function testFinalConstantsAddedFromReflection(): void
    {
        $reflector      = new ClassReflection(TestAsset\ClassWithFinalConst::class);
        $classGenerator = ClassGenerator::fromReflection($reflector);

        $constant = $classGenerator->getConstant('FINAL');
        self::assertNotFalse($constant);

        $constantValue = $constant->getDefaultValue();
        self::assertNotNull($constantValue);
        self::assertSame('const', $constantValue->getValue());

        self::assertTrue($constant->isFinal());
    }

    public function testFromArrayWithFinalConst(): void
    {
        $classGenerator = ClassGenerator::fromArray([
            'name'       => 'ClassWithFinalConst',
            'properties' => [
                [
                    'FINAL',
                    'const',
                    PropertyGenerator::FLAG_CONSTANT |
                    PropertyGenerator::FLAG_PUBLIC |
                    PropertyGenerator::FLAG_FINAL,
                ],
            ],
        ]);

        $expectedOutput = <<<EOS
class ClassWithFinalConst
{
    final public const FINAL = 'const';
}

EOS;

        $output = $classGenerator->generate();
        self::assertSame($expectedOutput, $output, $output);
    }

    public function testGenerateClassWithPromotedConstructorParameter(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('ClassWithPromotedParameter');

        $classGenerator->addMethod('__construct', [
            new PromotedParameterGenerator(
                'bar',
                'Foo',
                PromotedParameterGenerator::VISIBILITY_PRIVATE,
            ),
        ]);

        $expectedOutput = <<<EOS
class ClassWithPromotedParameter
{
    public function __construct(private \Foo \$bar)
    {
    }
}

EOS;

        self::assertEquals($expectedOutput, $classGenerator->generate());
    }

    public function testClassWithPromotedParameterFromReflection(): void
    {
        $classGenerator = ClassGenerator::fromReflection(
            new ClassReflection(ClassWithPromotedParameter::class)
        );

        $expectedOutput = <<<EOS
namespace LaminasTest\Code\Generator\TestAsset;

final class ClassWithPromotedParameter
{
    public function __construct(private string \$promotedParameter)
    {
    }
}

EOS;

        self::assertEquals($expectedOutput, $classGenerator->generate());
    }

    public function testFailToGenerateClassWithPromotedParameterOnNonConstructorMethod(): void
    {
        $classGenerator = new ClassGenerator();
        $classGenerator->setName('promotedParameterOnNonConstructorMethod');

        $this->expectExceptionObject(
            new InvalidArgumentException('Promoted parameter can only be added to constructor.')
        );

        $classGenerator->addMethod('thisIsNoConstructor', [
            new PromotedParameterGenerator('promotedParameter', 'string'),
        ]);
    }

    #[RequiresPhp('>= 8.2')]
    public function testReadonlyClassWithPromotedParameterFromReflection(): void
    {
        $classGenerator = ClassGenerator::fromReflection(
            new ClassReflection(ReadonlyClassWithPromotedParameter::class)
        );

        $expectedOutput = <<<EOS
namespace LaminasTest\Code\Generator\TestAsset;

final readonly class ReadonlyClassWithPromotedParameter
{
    public function __construct(private string \$promotedParameter)
    {
    }
}

EOS;

        self::assertEquals($expectedOutput, $classGenerator->generate());
    }

    #[RequiresPhp('>= 8.2')]
    public function testDnfClass(): void
    {
        $classGenerator = ClassGenerator::fromReflection(
            new ClassReflection(ClassWithDnfTypes::class)
        );

        // @phpcs:disable Generic.Files.LineLength
        $expectedOutput = <<<EOS
namespace LaminasTest\Code\Generator\TestAsset;

final class ClassWithDnfTypes
{
    public function __construct(private (\LaminasTest\Code\Generator\TestAsset\ThreeInterface&\LaminasTest\Code\Generator\TestAsset\TwoInterface)|\LaminasTest\Code\Generator\TestAsset\OneInterface \$promotedParameter)
    {
    }
}

EOS;
        // @phpcs:enable

        self::assertEquals($expectedOutput, $classGenerator->generate());
    }
}
