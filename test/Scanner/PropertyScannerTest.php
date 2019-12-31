<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Scanner;

use Laminas\Code\Scanner\FileScanner;
use PHPUnit_Framework_TestCase as TestCase;

class PropertyScannerTest extends TestCase
{
    public function testPropertyScannerHasPropertyInformation()
    {
        $file = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class = $file->getClass('LaminasTest\Code\TestAsset\FooClass');

        $property = $class->getProperty('bar');
        $this->assertEquals('bar', $property->getName());
        $this->assertEquals('value', $property->getValue());
        $this->assertFalse($property->isPublic());
        $this->assertTrue($property->isProtected());
        $this->assertFalse($property->isPrivate());
        $this->assertTrue($property->isStatic());

        $property = $class->getProperty('foo');
        $this->assertEquals('foo', $property->getName());
        $this->assertEquals('value2', $property->getValue());
        $this->assertTrue($property->isPublic());
        $this->assertFalse($property->isProtected());
        $this->assertFalse($property->isPrivate());
        $this->assertFalse($property->isStatic());

        $property = $class->getProperty('baz');
        $this->assertEquals('baz', $property->getName());
        $this->assertEquals(3, $property->getValue());
        $this->assertFalse($property->isPublic());
        $this->assertFalse($property->isProtected());
        $this->assertTrue($property->isPrivate());
        $this->assertFalse($property->isStatic());
    }
}
