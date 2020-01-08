<?php

/**
 * @see       https://github.com/laminasframwork/laminas-code for the canonical source repository
 * @copyright https://github.com/laminasframwork/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminasframwork/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Scanner;

use Laminas\Code\Scanner\AggregateDirectoryScanner;
use Laminas\Code\Scanner\DirectoryScanner;
use PHPUnit\Framework\TestCase;

class DerivedClassScannerTest extends TestCase
{
    public function testCreatesClass()
    {
        $ds = new DirectoryScanner();
        $ds->addDirectory(__DIR__ . '/TestAsset');
        $ads = new AggregateDirectoryScanner();
        $ads->addDirectoryScanner($ds);
        $c = $ads->getClass(TestAsset\MapperExample\RepositoryB::class);
        self::assertEquals(TestAsset\MapperExample\RepositoryB::class, $c->getName());
    }
}
