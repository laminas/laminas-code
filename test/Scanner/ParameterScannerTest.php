<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Scanner;

use Laminas\Code\Scanner\FileScanner;
use PHPUnit_Framework_TestCase as TestCase;

class ParameterScannerTest extends TestCase
{
    public function testParameterScannerHasParameterInformation()
    {
        $file      = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class     = $file->getClass('LaminasTest\Code\TestAsset\BarClass');
        $method    = $class->getMethod('three');
        $parameter = $method->getParameter('t');
        $this->assertEquals('LaminasTest\Code\TestAsset\BarClass', $parameter->getDeclaringClass());
        $this->assertEquals('three', $parameter->getDeclaringFunction());
        $this->assertEquals('t', $parameter->getName());
        $this->assertEquals(2, $parameter->getPosition());
        $this->assertEquals('2', $parameter->getDefaultValue());
        $this->assertFalse($parameter->isArray());
        $this->assertTrue($parameter->isDefaultValueAvailable());
        $this->assertTrue($parameter->isOptional());
        $this->assertTrue($parameter->isPassedByReference());
    }
}
