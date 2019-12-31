<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection;

use Laminas\Code\Reflection\ClassReflection;

/**
 * @category   Laminas
 * @package    Laminas_Reflection
 * @subpackage UnitTests
 *
 * @group      Laminas_Reflection
 * @group      Laminas_Reflection_Class
 */
class ClassReflectionTest extends \PHPUnit_Framework_TestCase
{


    public function testMethodReturns()
    {

        $reflectionClass = new ClassReflection('LaminasTest\Code\Reflection\TestAsset\TestSampleClass2');

        $methodByName = $reflectionClass->getMethod('getProp1');
        $this->assertEquals('Laminas\Code\Reflection\MethodReflection', get_class($methodByName));

        $methodsAll = $reflectionClass->getMethods();
        $this->assertEquals(3, count($methodsAll));

        $firstMethod = array_shift($methodsAll);
        $this->assertEquals('getProp1', $firstMethod->getName());
    }

    public function testPropertyReturns()
    {
        $reflectionClass = new ClassReflection('LaminasTest\Code\Reflection\TestAsset\TestSampleClass2');

        $propertyByName = $reflectionClass->getProperty('_prop1');
        $this->assertInstanceOf('Laminas\Code\Reflection\PropertyReflection', $propertyByName);

        $propertiesAll = $reflectionClass->getProperties();
        $this->assertEquals(2, count($propertiesAll));

        $firstProperty = array_shift($propertiesAll);
        $this->assertEquals('_prop1', $firstProperty->getName());
    }

    public function testParentReturn()
    {
        $reflectionClass = new ClassReflection('LaminasTest\Code\Reflection\TestAsset\TestSampleClass');

        $parent = $reflectionClass->getParentClass();
        $this->assertEquals('Laminas\Code\Reflection\ClassReflection', get_class($parent));
        $this->assertEquals('ArrayObject', $parent->getName());

    }

    public function testInterfaceReturn()
    {
        $reflectionClass = new ClassReflection('LaminasTest\Code\Reflection\TestAsset\TestSampleClass4');

        $interfaces = $reflectionClass->getInterfaces();
        $this->assertEquals(1, count($interfaces));

        $interface = array_shift($interfaces);
        $this->assertEquals('LaminasTest\Code\Reflection\TestAsset\TestSampleClassInterface', $interface->getName());

    }

    public function testGetContentsReturnsContents()
    {
        $reflectionClass = new ClassReflection('LaminasTest\Code\Reflection\TestAsset\TestSampleClass2');
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
        $reflectionClass = new ClassReflection('LaminasTest\Code\Reflection\TestAsset\TestSampleClass9');
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
        $reflectionClass = new ClassReflection('LaminasTest\Code\Reflection\TestAsset\TestSampleClass5');

        $this->assertEquals(18, $reflectionClass->getStartLine());
        $this->assertEquals(5, $reflectionClass->getStartLine(true));
    }


    public function testGetDeclaringFileReturnsFilename()
    {
        $reflectionClass = new ClassReflection('LaminasTest\Code\Reflection\TestAsset\TestSampleClass2');
        $this->assertContains('TestSampleClass2.php', $reflectionClass->getDeclaringFile()->getFileName());
    }

}
