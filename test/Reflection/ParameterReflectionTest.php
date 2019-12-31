<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection;

use Laminas\Code\Reflection;

/**
 * @category   Laminas
 * @package    Laminas_Reflection
 * @subpackage UnitTests
 * @group      Laminas_Reflection
 * @group      Laminas_Reflection_Parameter
 */
class ParameterReflectionTest extends \PHPUnit_Framework_TestCase
{
    public function testDeclaringClassReturn()
    {
        $parameter = new Reflection\ParameterReflection(array('LaminasTest\Code\Reflection\TestAsset\TestSampleClass2', 'getProp2'), 0);
        $this->assertInstanceOf('Laminas\Code\Reflection\ClassReflection', $parameter->getDeclaringClass());
    }

    public function testClassReturn_NoClassGiven_ReturnsNull()
    {
        $parameter = new Reflection\ParameterReflection(array('LaminasTest\Code\Reflection\TestAsset\TestSampleClass2', 'getProp2'), 'param1');
        $this->assertNull($parameter->getClass());
    }

    public function testClassReturn()
    {
        $parameter = new Reflection\ParameterReflection(array('LaminasTest\Code\Reflection\TestAsset\TestSampleClass2', 'getProp2'), 'param2');
        $this->assertInstanceOf('Laminas\Code\Reflection\ClassReflection', $parameter->getClass());
    }

    /**
     * @dataProvider paramTypeTestProvider
     */
    public function testTypeReturn($param, $type)
    {
        $parameter = new Reflection\ParameterReflection(array('LaminasTest\Code\Reflection\TestAsset\TestSampleClass5', 'doSomething'), $param);
        $this->assertEquals($type, $parameter->getType());
    }

    public function testCallableTypeHint()
    {
        if (PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('`callable` is only supported in PHP >=5.4.0');
        }

        $parameter = new Reflection\ParameterReflection(array('LaminasTest\Code\Reflection\TestAsset\CallableTypeHintClass', 'foo'), 'bar');

        $this->assertEquals('callable', $parameter->getType());
    }

    public function paramTypeTestProvider()
    {
        return array(
            array('one','int'),
            array('two','int'),
            array('three','string'),
            array('array','array'),
            array('class','LaminasTest\Code\Reflection\TestAsset\TestSampleClass')
        );
    }
}
