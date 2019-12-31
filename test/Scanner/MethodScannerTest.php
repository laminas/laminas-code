<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Scanner;

use Laminas\Code\Scanner\FileScanner;
use PHPUnit_Framework_TestCase as TestCase;

class MethodScannerTest extends TestCase
{
    public function testMethodScannerHasMethodInformation()
    {
        $file   = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class  = $file->getClass('LaminasTest\Code\TestAsset\FooClass');
        $method = $class->getMethod('fooBarBaz');
        $this->assertEquals('fooBarBaz', $method->getName());
        $this->assertFalse($method->isAbstract());
        $this->assertTrue($method->isFinal());
        $this->assertTrue($method->isPublic());
        $this->assertFalse($method->isProtected());
        $this->assertFalse($method->isPrivate());
        $this->assertFalse($method->isStatic());
    }

    public function testMethodScannerReturnsParameters()
    {
        $file       = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class      = $file->getClass('LaminasTest\Code\TestAsset\BarClass');
        $method     = $class->getMethod('three');
        $parameters = $method->getParameters();
        $this->assertInternalType('array', $parameters);
    }

    public function testMethodScannerReturnsParameterScanner()
    {
        $file   = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class  = $file->getClass('LaminasTest\Code\TestAsset\BarClass');
        $method = $class->getMethod('three');
        $this->assertEquals(array('o', 't', 'bbf'), $method->getParameters());
        $parameter = $method->getParameter('t');
        $this->assertInstanceOf('Laminas\Code\Scanner\ParameterScanner', $parameter);
        $this->assertEquals('t', $parameter->getName());
    }

    public function testMethodScannerReturnsPropertyWithNoDefault()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/BazClass.php');
        $class = $file->getClass('BazClass');
        $method = $class->getMethod('__construct');
        $this->assertTrue($method->isPublic());
    }

    public function testMethodScannerReturnsLineNumbersForMethods()
    {
        $file       = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class      = $file->getClass('LaminasTest\Code\TestAsset\BarClass');
        $method     = $class->getMethod('three');
        $this->assertEquals(27, $method->getLineStart());
        $this->assertEquals(31, $method->getLineEnd());
    }

    public function testMethodScannerReturnsBodyMethods()
    {
        $file     = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class    = $file->getClass('LaminasTest\Code\TestAsset\BarClass');
        $method   = $class->getMethod('three');
        $expected = "\n" . '        $x = 5 + 5;' . "\n" . '        $y = \'this string\';' . "\n    ";
        $this->assertEquals($expected, $method->getBody());
    }

    public function testMethodScannerMethodSignatureLatestOptionalParamHasParentheses()
    {
        $file       = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class      = $file->getClass('LaminasTest\Code\TestAsset\BarClass');
        $method = $class->getMethod('four');
        $paramTwo = $method->getParameter(1);
        $optionalValue = $paramTwo->getDefaultValue();
        $this->assertEquals('array(array(array(\'default\')))', $optionalValue);
    }
}
