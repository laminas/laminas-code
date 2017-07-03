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
use ZendTest\Code\TestAsset\FooClass;

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
