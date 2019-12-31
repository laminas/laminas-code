<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Scanner;

use Laminas\Code\Scanner\FileScanner;
use PHPUnit_Framework_TestCase as TestCase;

class FileScannerTest extends TestCase
{
    public function testFileScannerCanReturnClasses()
    {
        $tokenScanner = new FileScanner(__DIR__ . '/../TestAsset/MultipleNamespaces.php');
        $this->assertEquals('LaminasTest\Code\TestAsset\Baz', $tokenScanner->getClass('LaminasTest\Code\TestAsset\Baz')->getName());
        $this->assertEquals('Foo', $tokenScanner->getClass('Foo')->getName());
    }
}
