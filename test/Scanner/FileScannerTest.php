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
use ZendTest\Code\TestAsset\Baz;

class FileScannerTest extends TestCase
{
    public function testFileScannerCanReturnClasses()
    {
        $tokenScanner = new FileScanner(__DIR__ . '/../TestAsset/MultipleNamespaces.php');
        $this->assertEquals(Baz::class, $tokenScanner->getClass(Baz::class)->getName());
        $this->assertEquals('Foo', $tokenScanner->getClass('Foo')->getName());
    }
}
