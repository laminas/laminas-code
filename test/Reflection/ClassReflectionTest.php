<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Reflection;

use PHPUnit\Framework\TestCase;
use Zend\Code\Annotation\AnnotationManager;
use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Reflection\MethodReflection;
use Zend\Code\Reflection\PropertyReflection;
use Zend\Code\Scanner\FileScanner;
use ZendTest\Code\Reflection\TestAsset\InjectableClassReflection;

use function array_shift;
use function get_class;
use function trim;
use function uniqid;

/**
 * @group Zend_Reflection
 * @group Zend_Reflection_Class
 */
class ClassReflectionTest extends TestCase
{
    public function testMethodReturns()
    {
        $reflectionClass = new ClassReflection(TestAsset\TestSampleClass2::class);

        $methodByName = $reflectionClass->getMethod('getProp1');
        self::assertEquals(MethodReflection::class, get_class($methodByName));

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
        self::assertEquals(ClassReflection::class, get_class($parent));
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
        $target = <<<EOS
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

    public function getIterator()
    {
        return [];
    }

}
EOS;
        $contents = $reflectionClass->getContents();
        self::assertEquals(trim($target), trim($contents));
    }

    public function testGetContentsReturnsContentsWithImplementsOnSeparateLine()
    {
        $reflectionClass = new ClassReflection(TestAsset\TestSampleClass9::class);
        $target = <<<EOS
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

    public function getIterator()
    {
        return [];
    }

}
EOS;
        $contents = $reflectionClass->getContents();
        self::assertEquals(trim($target), trim($contents));
    }

    public function testStartLine()
    {
        $reflectionClass = new ClassReflection(TestAsset\TestSampleClass5::class);

        self::assertEquals(18, $reflectionClass->getStartLine());
        self::assertEquals(5, $reflectionClass->getStartLine(true));
    }

    public function testGetDeclaringFileReturnsFilename()
    {
        $reflectionClass = new ClassReflection(TestAsset\TestSampleClass2::class);
        self::assertContains('TestSampleClass2.php', $reflectionClass->getDeclaringFile()->getFileName());
    }

    public function testGetAnnotationsWithNoNameInformations()
    {
        $reflectionClass = new InjectableClassReflection(
            // TestSampleClass5 has the annotations required to get to the
            // right point in the getAnnotations method.
            TestAsset\TestSampleClass5::class
        );

        $annotationManager = new AnnotationManager();

        $fileScanner = $this->getMockBuilder(FileScanner::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $reflectionClass->setFileScanner($fileScanner);

        $fileScanner->method('getClassNameInformation')
                    ->willReturn(false);

        self::assertFalse($reflectionClass->getAnnotations($annotationManager));
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
        $traitsArray = $reflectionClass->getTraits();
        self::assertIsArray($traitsArray);
        self::assertCount(1, $traitsArray);
        self::assertInstanceOf(ClassReflection::class, $traitsArray[0]);

        $reflectionClass = new ClassReflection(TestAsset\TestSampleClass::class);
        $traitsArray = $reflectionClass->getTraits();
        self::assertIsArray($traitsArray);
        self::assertCount(0, $traitsArray);
    }
}
