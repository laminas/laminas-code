<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Scanner;

use Laminas\Code\Scanner\FileScanner;
use LaminasTest\Code\TestAsset\FooClass;
use PHPUnit\Framework\TestCase;

class ConstantScannerTest extends TestCase
{
    public function testConstantScannerHasConstantInformation()
    {
        $file = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class = $file->getClass(FooClass::class);

        $constant = $class->getConstant('BAR');
        self::assertEquals('BAR', $constant->getName());
        self::assertEquals(5, $constant->getValue());

        $constant = $class->getConstant('FOO');
        self::assertEquals('FOO', $constant->getName());
        self::assertEquals(5, $constant->getValue());

        $constant = $class->getConstant('BAZ');
        self::assertEquals('BAZ', $constant->getName());
        self::assertEquals('baz', $constant->getValue());
        self::assertNotNull('Some comment', $constant->getDocComment());
    }
}
