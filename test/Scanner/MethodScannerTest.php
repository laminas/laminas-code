<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Scanner;

use PHPUnit\Framework\TestCase;
use Zend\Code\Scanner\FileScanner;
use Zend\Code\Scanner\ParameterScanner;
use Zend\Code\Scanner\MethodScanner;
use ZendTest\Code\TestAsset\AbstractClass;
use ZendTest\Code\TestAsset\BarClass;
use ZendTest\Code\TestAsset\FooClass;

class MethodScannerTest extends TestCase
{
    public function testMethodScannerHasMethodInformation()
    {
        $file   = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class  = $file->getClass(FooClass::class);
        $method = $class->getMethod('fooBarBaz');
        self::assertEquals('fooBarBaz', $method->getName());
        self::assertFalse($method->isAbstract());
        self::assertTrue($method->isFinal());
        self::assertTrue($method->isPublic());
        self::assertFalse($method->isProtected());
        self::assertFalse($method->isPrivate());
        self::assertFalse($method->isStatic());
    }

    public function testMethodScannerReturnsParameters()
    {
        $file       = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class      = $file->getClass(BarClass::class);
        $method     = $class->getMethod('three');
        $parameters = $method->getParameters();
        self::assertInternalType('array', $parameters);
    }

    public function testMethodScannerReturnsParameterScanner()
    {
        $file   = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class  = $file->getClass(BarClass::class);
        $method = $class->getMethod('three');
        self::assertEquals(['o', 't', 'bbf'], $method->getParameters());
        $parameter = $method->getParameter('t');
        self::assertInstanceOf(ParameterScanner::class, $parameter);
        self::assertEquals('t', $parameter->getName());
    }

    public function testMethodScannerParsesClassNames()
    {
        $file   = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class  = $file->getClass(BarClass::class);
        $method = $class->getMethod('five');
        self::assertEquals(['a'], $method->getParameters());
        $parameter = $method->getParameter('a');
        self::assertEquals(AbstractClass::class, $parameter->getClass());
    }

    public function testMethodScannerReturnsPropertyWithNoDefault()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/BazClass.php');
        $class = $file->getClass('BazClass');
        $method = $class->getMethod('__construct');
        self::assertTrue($method->isPublic());
    }

    public function testMethodScannerReturnsLineNumbersForMethods()
    {
        $file       = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class      = $file->getClass(BarClass::class);
        $method     = $class->getMethod('three');
        self::assertEquals(27, $method->getLineStart());
        self::assertEquals(31, $method->getLineEnd());
    }

    public function testMethodScannerReturnsBodyMethods()
    {
        $file     = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class    = $file->getClass(BarClass::class);
        $method   = $class->getMethod('three');
        $expected = "\n" . '        $x = 5 + 5;' . "\n" . '        $y = \'this string\';' . "\n    ";
        self::assertEquals($expected, $method->getBody());
    }

    public function testMethodScannerMethodSignatureLatestOptionalParamHasParentheses()
    {
        $file       = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class      = $file->getClass(BarClass::class);
        $method = $class->getMethod('four');
        $paramTwo = $method->getParameter(1);
        $optionalValue = $paramTwo->getDefaultValue();
        self::assertEquals('array([array(\'default\')])', $optionalValue);
    }

    /**
     * @group issue-6893
     */
    public function testMethodScannerWorksWithSingleAbstractFunction()
    {
        $file = new FileScanner(__DIR__ . '/../TestAsset/AbstractClass.php');

        $class = $file->getClass(AbstractClass::class);
        $method = $class->getMethod('helloWorld');

        self::assertTrue($method->isAbstract());
    }

    /**
     * @expectedException \Zend\Code\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid visibility argument passed to setVisibility.
     */

    public function testMethodScannerSetVisibilityThrowsInvalidArgumentException()
    {
        $methodScanner = new MethodScanner([]);

        $invalidArgument = 42;
        self::assertTrue(! in_array($invalidArgument, [T_PUBLIC, T_PROTECTED, T_PRIVATE]));
        $methodScanner->setVisibility(42);
    }

    public function testMethodScannerSetVisibilityAcceptsIntegerTokens()
    {
        $methodScanner = new MethodScanner([]);

        self::assertTrue($methodScanner->setVisibility(T_PUBLIC) === $methodScanner);
        self::assertTrue($methodScanner->setVisibility(T_PROTECTED) === $methodScanner);
        self::assertTrue($methodScanner->setVisibility(T_PRIVATE) === $methodScanner);
    }
}
