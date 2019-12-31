<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Scanner;

use Laminas\Code\Scanner\FileScanner;
use LaminasTest\Code\TestAsset\Baz;
use PHPUnit\Framework\TestCase;

class FileScannerTest extends TestCase
{
    public function testFileScannerCanReturnClasses()
    {
        $tokenScanner = new FileScanner(__DIR__ . '/../TestAsset/MultipleNamespaces.php');
        self::assertEquals(Baz::class, $tokenScanner->getClass(Baz::class)->getName());
        self::assertEquals('Foo', $tokenScanner->getClass('Foo')->getName());
    }
}
