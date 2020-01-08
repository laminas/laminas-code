<?php

/**
 * @see       https://github.com/laminasframwork/laminas-code for the canonical source repository
 * @copyright https://github.com/laminasframwork/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminasframwork/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Scanner;

use Laminas\Code\Scanner\FileScanner;
use LaminasTest\Code\TestAsset\BarClass;
use PHPUnit\Framework\TestCase;

class ParameterScannerTest extends TestCase
{
    public function testParameterScannerHasParameterInformation()
    {
        $file      = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class     = $file->getClass(BarClass::class);
        $method    = $class->getMethod('three');
        $parameter = $method->getParameter('t');
        self::assertEquals(BarClass::class, $parameter->getDeclaringClass());
        self::assertEquals('three', $parameter->getDeclaringFunction());
        self::assertEquals('t', $parameter->getName());
        self::assertEquals(2, $parameter->getPosition());
        self::assertEquals('2', $parameter->getDefaultValue());
        self::assertFalse($parameter->isArray());
        self::assertTrue($parameter->isDefaultValueAvailable());
        self::assertTrue($parameter->isOptional());
        self::assertTrue($parameter->isPassedByReference());
    }
}
