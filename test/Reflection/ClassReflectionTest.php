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

/**
 *
 * @group      Zend_Reflection
 * @group      Zend_Reflection_Class
 */
class ClassReflectionTest extends TestCase
{
    public function testMethodReturns()
    {
        $reflectionClass = new ClassReflection(TestAsset\TestSampleClass2::class);

        $methodByName = $reflectionClass->getMethod('getProp1');
        $this->assertEquals(MethodReflection::class, get_class($methodByName));

        $methodsAll = $reflectionClass->getMethods();
        $this->assertCount(3, $methodsAll);

        $firstMethod = array_shift($methodsAll);
        $this->assertEquals('getProp1', $firstMethod->getName());
    }

    public function testPropertyReturns()
    {
        $reflectionClass = new ClassReflection(TestAsset\TestSampleClass2::class);

        $propertyByName = $reflectionClass->getProperty('_prop1');
        $this->assertInstanceOf(PropertyReflection::class, $propertyByName);

        $propertiesAll = $reflectionClass->getProperties();
        $this->assertCount(2, $propertiesAll);

        $firstProperty = array_shift($propertiesAll);
        $this->assertEquals('_prop1', $firstProperty->getName());
    }

    public function testParentReturn()
    {
        $reflectionClass = new ClassReflection(TestAsset\TestSampleClass::class);

        $parent = $reflectionClass->getParentClass();
        $this->assertEquals(ClassReflection::class, get_class($parent));
        $this->assertEquals('ArrayObject', $parent->getName());
    }

    public function testInterfaceReturn()
    {
        $reflectionClass = new ClassReflection(TestAsset\TestSampleClass4::class);

        $interfaces = $reflectionClass->getInterfaces();
        $this->assertCount(1, $interfaces);

        $interface = array_shift($interfaces);
        $this->assertEquals(TestAsset\TestSampleClassInterface::class, $interface->getName());
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
        return array();
    }

}
EOS;
        $contents = $reflectionClass->getContents();
        $this->assertEquals(trim($target), trim($contents));
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
        return array();
    }

}
EOS;
        $contents = $reflectionClass->getContents();
        $this->assertEquals(trim($target), trim($contents));
    }

    public function testStartLine()
    {
        $reflectionClass = new ClassReflection(TestAsset\TestSampleClass5::class);

        $this->assertEquals(18, $reflectionClass->getStartLine());
        $this->assertEquals(5, $reflectionClass->getStartLine(true));
    }

    public function testGetDeclaringFileReturnsFilename()
    {
        $reflectionClass = new ClassReflection(TestAsset\TestSampleClass2::class);
        $this->assertContains('TestSampleClass2.php', $reflectionClass->getDeclaringFile()->getFileName());
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

        $fileScanner->expects($this->any())
                    ->method('getClassNameInformation')
                    ->will($this->returnValue(false));

        $this->assertFalse($reflectionClass->getAnnotations($annotationManager));
    }

    public function testGetContentsReturnsEmptyContentsOnEvaldCode()
    {
        $className = uniqid('ClassReflectionTestGenerated');

        eval('name' . 'space ' . __NAMESPACE__ . '; cla' . 'ss ' . $className . '{}');

        $reflectionClass = new ClassReflection(__NAMESPACE__ . '\\' . $className);

        $this->assertSame('', $reflectionClass->getContents());
    }

    public function testGetContentsReturnsEmptyContentsOnInternalCode()
    {
        $reflectionClass = new ClassReflection('ReflectionClass');
        $this->assertSame('', $reflectionClass->getContents());
    }

    public function testGetTraits()
    {
        // PHP documentations mentions that getTraits() return NULL in case of error. I don't know how to cause such
        // error so I test just normal behaviour.

        $reflectionClass = new ClassReflection(TestAsset\TestTraitClass4::class);
        $traitsArray = $reflectionClass->getTraits();
        $this->assertInternalType('array', $traitsArray);
        $this->assertCount(1, $traitsArray);
        $this->assertInstanceOf(ClassReflection::class, $traitsArray[0]);

        $reflectionClass = new ClassReflection(TestAsset\TestSampleClass::class);
        $traitsArray = $reflectionClass->getTraits();
        $this->assertInternalType('array', $traitsArray);
        $this->assertCount(0, $traitsArray);
    }
}
