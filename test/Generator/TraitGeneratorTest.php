<?php

namespace LaminasTest\Code\Generator;

use DateTime;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\Exception\ExceptionInterface;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\FileGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\TraitGenerator;
use Laminas\Code\Reflection\ClassReflection;
use LaminasTest\Code\Generator\TestAsset\PrototypeClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use Serializable;
use Throwable;

use function current;

#[Group('Laminas_Code_Generator')]
#[Group('Laminas_Code_Generator_Php')]
class TraitGeneratorTest extends TestCase
{
    public function testConstruction(): void
    {
        $class = new TraitGenerator();
        self::assertInstanceOf(TraitGenerator::class, $class);
    }

    public function testNameAccessors(): void
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setName('TestClass');
        self::assertSame('TestClass', $classGenerator->getName());
    }

    public function testClassDocBlockAccessors(): void
    {
        $docBlock = new DocBlockGenerator('some description');

        $classGenerator = new TraitGenerator();
        $classGenerator->setDocBlock($docBlock);

        self::assertSame($docBlock, $classGenerator->getDocBlock());
    }

    public function testAbstractAccessorsReturnsFalse(): void
    {
        $classGenerator = new TraitGenerator();
        self::assertFalse($classGenerator->isAbstract());
        $classGenerator->setAbstract(true);
        self::assertFalse($classGenerator->isAbstract());
    }

    public function testExtendedClassAccessors(): void
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setExtendedClass('ExtendedClass');
        self::assertNull($classGenerator->getExtendedClass());
    }

    public function testAddFlagDoesNothing(): void
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addFlag(ClassGenerator::FLAG_FINAL);

        self::assertSame(0x00, $this->getFlags($classGenerator));
    }

    public function testSetFlagsDoesNothing(): void
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setFlags(ClassGenerator::FLAG_FINAL);

        self::assertSame(0x00, $this->getFlags($classGenerator));
    }

    public function testRemoveFlagDoesNothing(): void
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addFlag(ClassGenerator::FLAG_ABSTRACT);
        $classGenerator->addFlag(ClassGenerator::FLAG_FINAL);
        $classGenerator->removeFlag(ClassGenerator::FLAG_ABSTRACT);

        self::assertSame(0x00, $this->getFlags($classGenerator));
    }

    public function testSetFinalToTrueDoesNothing(): void
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setFinal(true);

        self::assertSame(false, $classGenerator->isFinal());
    }

    public function testImplementedInterfacesAccessors(): void
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->setImplementedInterfaces([Serializable::class, Throwable::class]);
        self::assertCount(0, $classGenerator->getImplementedInterfaces());
    }

    public function testPropertyAccessors(): void
    {
        $classGenerator = new TraitGenerator();
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
        $classGenerator = new TraitGenerator();
        $classGenerator->addProperty('prop3');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A property by name prop3 already exists in this class');
        $classGenerator->addProperty('prop3');
    }

    public function testSetPropertyNoArrayOrPropertyThrowsException(): void
    {
        $classGenerator = new TraitGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Laminas\Code\Generator\TraitGenerator::addProperty expects string for name');
        $classGenerator->addProperty(true);
    }

    public function testMethodAccessors(): void
    {
        $classGenerator = new TraitGenerator();
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
        $classGenerator = new TraitGenerator();

        $this->expectException(ExceptionInterface::class);
        $this->expectExceptionMessage('Laminas\Code\Generator\TraitGenerator::addMethod expects string for name');

        $classGenerator->addMethod(true);
    }

    public function testSetMethodNameAlreadyExistsThrowsException(): void
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

    #[Group('Laminas-7361')]
    public function testHasMethod(): void
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addMethod('methodOne');

        self::assertTrue($classGenerator->hasMethod('methodOne'));
    }

    public function testRemoveMethod(): void
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addMethod('methodOne');
        self::assertTrue($classGenerator->hasMethod('methodOne'));

        $classGenerator->removeMethod('methodOne');
        self::assertFalse($classGenerator->hasMethod('methodOne'));
    }

    #[Group('Laminas-7361')]
    public function testHasProperty(): void
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addProperty('propertyOne');

        self::assertTrue($classGenerator->hasProperty('propertyOne'));
    }

    public function testToString(): void
    {
        $classGenerator = TraitGenerator::fromArray([
            'name'       => 'SampleClass',
            'properties' => [
                'foo',
                ['name' => 'bar'],
            ],
            'methods'    => [
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
        self::assertSame($expectedOutput, $output, $output);
    }

    #[Group('Laminas-7909')]
    public function testClassFromReflectionThatImplementsInterfaces(): void
    {
        $reflClass = new ClassReflection(TestAsset\ClassWithInterface::class);

        $classGenerator = TraitGenerator::fromReflection($reflClass);
        $classGenerator->setSourceDirty(true);

        $code = $classGenerator->generate();

        $expectedClassDef = 'trait ClassWithInterface';
        self::assertStringContainsString($expectedClassDef, $code);
    }

    #[Group('Laminas-7909')]
    public function testClassFromReflectionDiscardParentImplementedInterfaces(): void
    {
        $reflClass = new ClassReflection(TestAsset\NewClassWithInterface::class);

        $classGenerator = TraitGenerator::fromReflection($reflClass);
        $classGenerator->setSourceDirty(true);

        $code = $classGenerator->generate();

        $expectedClassDef = 'trait NewClassWithInterface';
        self::assertStringContainsString($expectedClassDef, $code);
    }

    #[Group('4988')]
    public function testNonNamespaceClassReturnsAllMethods(): void
    {
        require_once __DIR__ . '/../TestAsset/NonNamespaceClass.php';

        $reflClass      = new ClassReflection('LaminasTest_Code_NsTest_BarClass');
        $classGenerator = TraitGenerator::fromReflection($reflClass);
        self::assertCount(1, $classGenerator->getMethods());
    }

    public function testNamespacedClassReturnsAllMethods(): void
    {
        $reflClass = new ClassReflection(PrototypeClass::class);

        $classGenerator = TraitGenerator::fromReflection($reflClass);
        self::assertCount(1, $classGenerator->getMethods());
    }

    #[Group('Laminas-9602')]
    public function testSetextendedclassShouldIgnoreEmptyClassnameOnGenerate(): void
    {
        $classGeneratorClass = new TraitGenerator();
        $classGeneratorClass
            ->setName('MyClass')
            ->setExtendedClass(null);

        $expected = <<<CODE
trait MyClass
{
}

CODE;
        self::assertSame($expected, $classGeneratorClass->generate());
    }

    #[Group('Laminas-9602')]
    public function testSetextendedclassShouldNotIgnoreNonEmptyClassnameOnGenerate(): void
    {
        $classGeneratorClass = new TraitGenerator();
        $classGeneratorClass
            ->setName('MyClass')
            ->setExtendedClass(DateTime::class);

        $expected = <<<CODE
trait MyClass
{
}

CODE;
        self::assertSame($expected, $classGeneratorClass->generate());
    }

    #[Group('namespace')]
    public function testCodeGenerationShouldTakeIntoAccountNamespacesFromReflection(): void
    {
        $reflClass      = new ClassReflection(TestAsset\ClassWithNamespace::class);
        $classGenerator = TraitGenerator::fromReflection($reflClass);
        self::assertSame('LaminasTest\Code\Generator\TestAsset', $classGenerator->getNamespaceName());
        self::assertSame('ClassWithNamespace', $classGenerator->getName());
        $expected = <<<CODE
namespace LaminasTest\Code\Generator\\TestAsset;

trait ClassWithNamespace
{
}

CODE;
        $received = $classGenerator->generate();
        self::assertSame($expected, $received, $received);
    }

    #[Group('namespace')]
    public function testSetNameShouldDetermineIfNamespaceSegmentIsPresent(): void
    {
        $classGeneratorClass = new TraitGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        self::assertSame('My\Namespaced', $classGeneratorClass->getNamespaceName());
    }

    #[Group('namespace')]
    public function testPassingANamespacedClassnameShouldGenerateANamespaceDeclaration(): void
    {
        $classGeneratorClass = new TraitGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        $received = $classGeneratorClass->generate();
        self::assertStringContainsString('namespace My\Namespaced;', $received, $received);
    }

    #[Group('namespace')]
    public function testPassingANamespacedClassnameShouldGenerateAClassnameWithoutItsNamespace(): void
    {
        $classGeneratorClass = new TraitGenerator();
        $classGeneratorClass->setName('My\Namespaced\FunClass');
        $received = $classGeneratorClass->generate();
        self::assertStringContainsString('trait FunClass', $received, $received);
    }

    #[Group('Laminas-151')]
    public function testAddUses(): void
    {
        $classGenerator = new TraitGenerator();
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
        $classGenerator = new TraitGenerator();
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
        $classGenerator = new TraitGenerator();
        $classGenerator->setName('My\Class');
        $classGenerator->addUse('My\First\Use\Class', 'MyAlias');
        $classGenerator->addUse('My\First\Use\Class', 'MyAlias');
        $generated = $classGenerator->generate();

        self::assertCount(1, $classGenerator->getUses());

        self::assertStringContainsString('use My\First\Use\Class as MyAlias;', $generated);
    }

    public function testCreateFromArrayWithContainingFileGeneratorInstance(): void
    {
        $classGenerator = TraitGenerator::fromArray([
            'name'           => 'SampleClass',
            'containingfile' => new FileGenerator(),
        ]);

        $fileGenerator = $classGenerator->getContainingFileGenerator();
        self::assertInstanceOf(FileGenerator::class, $fileGenerator);
    }

    public function testCreateFromArrayWithNamespace(): void
    {
        $namespace = "SomeNamespace";

        $classGenerator = TraitGenerator::fromArray([
            'name'          => 'SampleClass',
            'namespacename' => $namespace,
        ]);

        self::assertSame($namespace, $classGenerator->getNamespaceName());
    }

    public function testCreateFromArrayWithDocBlockFromArray(): void
    {
        $classGenerator = TraitGenerator::fromArray([
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
        $classGenerator = TraitGenerator::fromArray([
            'name'     => 'SampleClass',
            'docblock' => new DocBlockGenerator('foo'),
        ]);

        $docBlock = $classGenerator->getDocBlock();
        self::assertInstanceOf(DocBlockGenerator::class, $docBlock);
    }

    public function testCreateFromArrayWithoutNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class generator requires that a name is provided for this object');

        TraitGenerator::fromArray([
            'docblock' => new DocBlockGenerator('foo'),
        ]);
    }

    public function testExtendedClassProperies(): void
    {
        $reflClass      = new ClassReflection(TestAsset\ExtendedClassWithProperties::class);
        $classGenerator = TraitGenerator::fromReflection($reflClass);
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
        $classGenerator = new TraitGenerator();
        $classGenerator->addMethod('methodOne');

        self::assertTrue($classGenerator->hasMethod('methodOne'));
        self::assertTrue($classGenerator->hasMethod('MethoDonE'));
    }

    public function testRemoveMethodInsensitive(): void
    {
        $classGenerator = new TraitGenerator();
        $classGenerator->addMethod('methodOne');

        $classGenerator->removeMethod('METHODONe');
        self::assertFalse($classGenerator->hasMethod('methodOne'));
    }

    public function testGenerateClassAndAddMethod(): void
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
        self::assertSame($expected, $output);
    }

    /**
     * @return mixed
     * @throws ReflectionException
     */
    private function getFlags(TraitGenerator $classGenerator)
    {
        $reflectedClass = new ReflectionClass($classGenerator);

        $reflection = $reflectedClass->getProperty('flags');
        $reflection->setAccessible(true);

        return $reflection->getValue($classGenerator);
    }
}
