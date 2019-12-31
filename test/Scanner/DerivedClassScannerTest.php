<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Scanner;

use Laminas\Code\Scanner\AggregateDirectoryScanner;
use Laminas\Code\Scanner\DerivedClassScanner;
use Laminas\Code\Scanner\DirectoryScanner;

class DerivedClassScannerTest extends \PHPUnit_Framework_TestCase
{

    public function testCreatesClass()
    {
        $ds = new DirectoryScanner();
        $ds->addDirectory(__DIR__ . '/TestAsset');
        $ads = new AggregateDirectoryScanner();
        $ads->addDirectoryScanner($ds);
        $c = $ads->getClass('LaminasTest\Code\Scanner\TestAsset\MapperExample\RepositoryB');
        $this->assertEquals('LaminasTest\Code\Scanner\TestAsset\MapperExample\RepositoryB', $c->getName());
    }


}
