<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Scanner;

use Laminas\Code\Scanner\FileScanner;
use PHPUnit_Framework_TestCase as TestCase;

class ConstantScannerTest extends TestCase
{
    public function testConstantScannerHasConstantInformation()
    {
        $file = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class = $file->getClass('LaminasTest\Code\TestAsset\FooClass');

        $constant = $class->getConstant('BAR');
        $this->assertEquals('BAR', $constant->getName());
        $this->assertEquals(5, $constant->getValue());

        $constant = $class->getConstant('FOO');
        $this->assertEquals('FOO', $constant->getName());
        $this->assertEquals(5, $constant->getValue());

        $constant = $class->getConstant('BAZ');
        $this->assertEquals('BAZ', $constant->getName());
        $this->assertEquals('baz', $constant->getValue());
        $this->assertNotNull('Some comment', $constant->getDocComment());
    }
}
