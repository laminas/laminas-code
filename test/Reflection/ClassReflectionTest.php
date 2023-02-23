<?php

namespace LaminasTest\Code\Reflection;

use Laminas\Code\Reflection\ClassReflection;
use Laminas\Code\Reflection\MethodReflection;
use Laminas\Code\Reflection\PropertyReflection;
use LaminasTest\Code\Reflection\TestAsset\TestTraitClass3;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function array_shift;
use function get_class;
use function trim;
use function uniqid;

#[Group('Laminas_Reflection')]
#[Group('Laminas_Reflection_Class')]
class ClassReflectionTest extends TestCase
{
    public function testMethodReturns()
    {
        $reflectionClass = new ClassReflection(TestAsset\TestSampleClass2::class);

        $methodByName = $reflectionClass->getMethod('getProp1');
        self::assertEquals(MethodReflection::class, $methodByName::class);

        $methodsAll = $reflectionClass->getMethods();
        self::assertCount(3, $methodsAll);

        $firstMethod = array_shift($methodsAll);
        self::assertEquals('getProp1', $firstMethod->getName());
    }

    public function testPropertyReturns()
    {
        $reflectionClass = new ClassReflection(TestAsset\TestSampleClass2::class);

        $propertyByName = $reflectionClass->getProperty('_prop1');
        self::assertInstanceOf(PropertyReflection::class, $propertyByName);

        $propertiesAll = $reflectionClass->getProperties();
        self::assertCount(2, $propertiesAll);

        $firstProperty = array_shift($propertiesAll);
        self::assertEquals('_prop1', $firstProperty->getName());
    }

    public function testParentReturn()
    {
        $reflectionClass = new ClassReflection(TestAsset\TestSampleClass::class);

        $parent = $reflectionClass->getParentClass();
        self::assertEquals(ClassReflection::class, $parent::class);
        self::assertEquals('ArrayObject', $parent->getName());
    }

    public function testInterfaceReturn()
    {
        $reflectionClass = new ClassReflection(TestAsset\TestSampleClass4::class);

        $interfaces = $reflectionClass->getInterfaces();
        self::assertCount(1, $interfaces);

        $interface = array_shift($interfaces);
        self::assertEquals(TestAsset\TestSampleClassInterface::class, $interface->getName());
    }

    public function testGetContentsReturnsContents()
    {
        $reflectionClass = new ClassReflection(TestAsset\TestSampleClass2::class);
        $target          = <<<EOS
{
    protected \$_prop1 = null;

    /**
     * @Sample({"foo":"bar"})
     */
    protected \$_prop2 = null;

    public function getProp1()
    {
        return \$this->_prop1;
    }

    public function getProp2(\$param1, TestSampleClass \$param2)
    {
        return \$this->_prop2;
    }

    public function getIterator(): \Traversable
    {
        return new \EmptyIterator();
    }

}
EOS;
        $contents        = $reflectionClass->getContents();
        self::assertEquals(trim($target), trim($contents));
    }

    public function testGetContentsReturnsContentsWithImplementsOnSeparateLine()
    {
        $reflectionClass = new ClassReflection(TestAsset\TestSampleClass9::class);
        $target          = <<<EOS
{
    protected \$_prop1 = null;

    /**
     * @Sample({"foo":"bar"})
     */
    protected \$_prop2 = null;

    public function getProp1()
    {
        return \$this->_prop1;
    }

    public function getProp2(\$param1, TestSampleClass \$param2)
    {
        return \$this->_prop2;
    }

    public function getIterator(): \Traversable
    {
        return new \EmptyIterator();
    }

}
EOS;
        $contents        = $reflectionClass->getContents();
        self::assertEquals(trim($target), trim($contents));
    }

    public function testStartLine()
    {
        $reflectionClass = new ClassReflection(TestAsset\TestSampleClass5::class);

        self::assertEquals(18, $reflectionClass->getStartLine());
        self::assertEquals(5, $reflectionClass->getStartLine(true));
    }

    public function testGetContentsReturnsEmptyContentsOnEvaldCode()
    {
        $className = uniqid('ClassReflectionTestGenerated');

        eval('namespace ' . __NAMESPACE__ . '; class ' . $className . '{}');

        $reflectionClass = new ClassReflection(__NAMESPACE__ . '\\' . $className);

        self::assertSame('', $reflectionClass->getContents());
    }

    public function testGetContentsReturnsEmptyContentsOnInternalCode()
    {
        $reflectionClass = new ClassReflection('ReflectionClass');
        self::assertSame('', $reflectionClass->getContents());
    }

    public function testGetTraits()
    {
        // PHP documentations mentions that getTraits() return NULL in case of error. I don't know how to cause such
        // error so I test just normal behaviour.

        $reflectionClass = new ClassReflection(TestAsset\TestTraitClass4::class);
        $traitsArray     = $reflectionClass->getTraits();

        self::assertCount(1, $traitsArray);
        self::assertSame(TestTraitClass3::class, $traitsArray[TestTraitClass3::class]->getName());

        $reflectionClass = new ClassReflection(TestAsset\TestSampleClass::class);
        $traitsArray     = $reflectionClass->getTraits();

        self::assertEmpty($traitsArray);
    }
}
