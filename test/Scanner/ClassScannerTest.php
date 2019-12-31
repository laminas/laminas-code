<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Scanner;

use Laminas\Code\Annotation;
use Laminas\Code\Scanner\FileScanner;
use PHPUnit_Framework_TestCase as TestCase;

class ClassScannerTest extends TestCase
{
    protected $manager;

    public function setUp()
    {
        $this->manager = new Annotation\AnnotationManager();

        $genericParser = new Annotation\Parser\GenericAnnotationParser();
        $genericParser->registerAnnotation('LaminasTest\Code\Annotation\TestAsset\Foo');
        $genericParser->registerAnnotation('LaminasTest\Code\Annotation\TestAsset\Bar');

        $this->manager->attach($genericParser);
    }

    public function testClassScannerHasClassInformation()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class = $file->getClass('LaminasTest\Code\TestAsset\FooClass');
        $this->assertEquals('LaminasTest\Code\TestAsset\FooClass', $class->getName());
        $this->assertEquals('FooClass', $class->getShortName());
        $this->assertFalse($class->isFinal());
        $this->assertTrue($class->isAbstract());
        $this->assertFalse($class->isInterface());
        $interfaces = $class->getInterfaces();
        $this->assertContains('ArrayAccess', $interfaces);
        $this->assertContains('A\B\C\D\Blarg', $interfaces);
        $this->assertContains('LaminasTest\Code\TestAsset\Local\SubClass', $interfaces);
        $methods = $class->getMethodNames();
        $this->assertInternalType('array', $methods);
        $this->assertContains('fooBarBaz', $methods);
    }

    public function testClassScannerHasConstant()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class = $file->getClass('LaminasTest\Code\TestAsset\FooClass');
        $this->assertInternalType('array', $class->getConstants());
    }

    public function testClassScannerHasProperties()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class = $file->getClass('LaminasTest\Code\TestAsset\FooClass');
        $this->assertContains('bar', $class->getPropertyNames());
    }

    public function testClassScannerHasMethods()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class = $file->getClass('LaminasTest\Code\TestAsset\FooClass');
        $this->assertContains('fooBarBaz', $class->getMethodNames());
    }

    public function testClassScannerReturnsMethodsWithMethodScanners()
    {
        $file    = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class   = $file->getClass('LaminasTest\Code\TestAsset\FooClass');
        $methods = $class->getMethods();
        foreach ($methods as $method) {
            $this->assertInstanceOf('Laminas\Code\Scanner\MethodScanner', $method);
        }
    }

    public function testClassScannerReturnsPropertiesWithPropertyScanners()
    {
        $file    = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class   = $file->getClass('LaminasTest\Code\TestAsset\FooClass');
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $this->assertInstanceOf('Laminas\Code\Scanner\PropertyScanner', $property);
        }
    }

    public function testClassScannerCanScanInterface()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooInterface.php');
        $class = $file->getClass('LaminasTest\Code\TestAsset\FooInterface');
        $this->assertEquals('LaminasTest\Code\TestAsset\FooInterface', $class->getName());
    }

    public function testClassScannerCanReturnLineNumbers()
    {
        $file    = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class   = $file->getClass('LaminasTest\Code\TestAsset\FooClass');
        $this->assertEquals(11, $class->getLineStart());
        $this->assertEquals(31, $class->getLineEnd());

        $file    = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class   = $file->getClass('LaminasTest\Code\TestAsset\BarClass');
        $this->assertEquals(10, $class->getLineStart());
        $this->assertEquals(33, $class->getLineEnd());
    }

    public function testClassScannerCanScanAnnotations()
    {
        $file    = new FileScanner(__DIR__ . '/../Annotation/TestAsset/EntityWithAnnotations.php');
        $class   = $file->getClass('LaminasTest\Code\Annotation\TestAsset\EntityWithAnnotations');
        $annotations = $class->getAnnotations($this->manager);

        $this->assertTrue($annotations->hasAnnotation('LaminasTest\Code\Annotation\TestAsset\Foo'));
        $this->assertTrue($annotations->hasAnnotation('LaminasTest\Code\Annotation\TestAsset\Bar'));

        $this->assertEquals('first',  $annotations[0]->content);
        $this->assertEquals('second', $annotations[1]->content);
        $this->assertEquals('third',  $annotations[2]->content);
    }
}
