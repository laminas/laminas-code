<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Scanner;

use Laminas\Code\Annotation\AnnotationManager;
use Laminas\Code\Scanner\CachingFileScanner;
use LaminasTest\Code\TestAsset\BarClass;
use PHPUnit\Framework\TestCase;

use function count;

class CachingFileScannerTest extends TestCase
{
    protected function setUp() : void
    {
        CachingFileScanner::clearCache();
    }

    public function testCachingFileScannerWillUseSameInternalFileScannerWithMatchingFileNameAnAnnotationManagerObject()
    {
        CachingFileScanner::clearCache();

        // single entry, based on file
        $cfs1 = new CachingFileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        self::assertContains(BarClass::class, $cfs1->getClassNames());
        self::assertEquals(1, $this->getCacheCount($cfs1));

        // ensure same class is used internally
        $cfs2 = new CachingFileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        self::assertEquals(1, $this->getCacheCount($cfs2));
        self::assertSameInternalFileScanner($cfs1, $cfs2);

        // ensure
        $cfs3 = new CachingFileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        self::assertEquals(2, $this->getCacheCount($cfs3));
        self::assertDifferentInternalFileScanner($cfs2, $cfs3);

        $annoManager = new AnnotationManager();
        $cfs4 = new CachingFileScanner(__DIR__ . '/../TestAsset/FooClass.php', $annoManager);
        self::assertEquals(3, $this->getCacheCount($cfs4));
        self::assertDifferentInternalFileScanner($cfs3, $cfs4);

        $cfs5 = new CachingFileScanner(__DIR__ . '/../TestAsset/FooClass.php', $annoManager);
        self::assertEquals(3, $this->getCacheCount($cfs5));
        self::assertSameInternalFileScanner($cfs4, $cfs5);

        $cfs6 = new CachingFileScanner(__DIR__ . '/../TestAsset/BarClass.php', $annoManager);
        self::assertEquals(4, $this->getCacheCount($cfs6));
        self::assertDifferentInternalFileScanner($cfs5, $cfs6);
    }

    protected function getCacheCount(CachingFileScanner $cfs)
    {
        $r = new \ReflectionObject($cfs);
        $cacheProp = $r->getProperty('cache');
        $cacheProp->setAccessible(true);
        return count($cacheProp->getValue($cfs));
    }

    protected function assertSameInternalFileScanner(CachingFileScanner $one, CachingFileScanner $two)
    {
        $rOne = new \ReflectionObject($one);
        $fileScannerPropOne = $rOne->getProperty('fileScanner');
        $fileScannerPropOne->setAccessible(true);
        $rTwo = new \ReflectionObject($two);
        $fileScannerPropTwo = $rTwo->getProperty('fileScanner');
        $fileScannerPropTwo->setAccessible(true);
        self::assertSame($fileScannerPropOne->getValue($one), $fileScannerPropTwo->getValue($two));
    }

    protected function assertDifferentInternalFileScanner(CachingFileScanner $one, CachingFileScanner $two)
    {
        $rOne = new \ReflectionObject($one);
        $fileScannerPropOne = $rOne->getProperty('fileScanner');
        $fileScannerPropOne->setAccessible(true);
        $rTwo = new \ReflectionObject($two);
        $fileScannerPropTwo = $rTwo->getProperty('fileScanner');
        $fileScannerPropTwo->setAccessible(true);
        self::assertNotSame($fileScannerPropOne->getValue($one), $fileScannerPropTwo->getValue($two));
    }
}
