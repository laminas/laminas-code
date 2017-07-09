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
use Zend\Code\Scanner\ClassScanner;
use Zend\Code\Scanner\TokenArrayScanner;
use ZendTest\Code\TestAsset\Baz;
use ZendTest\Code\TestAsset\FooClass;
use ZendTest\Code\TestAsset\FooTrait;

use function file_get_contents;
use function token_get_all;

class TokenArrayScannerTest extends TestCase
{
    public function testScannerReturnsNamespaces()
    {
        $tokenScanner = new TokenArrayScanner(token_get_all(
            file_get_contents(__DIR__ . '/../TestAsset/FooClass.php')
        ));
        self::assertTrue($tokenScanner->hasNamespace('ZendTest\Code\TestAsset'));
        $namespaces = $tokenScanner->getNamespaces();
        self::assertInternalType('array', $namespaces);
        self::assertContains('ZendTest\Code\TestAsset', $namespaces);
    }

    public function testScannerReturnsNamespacesInNotNamespacedClasses()
    {
        $tokenScanner = new TokenArrayScanner(token_get_all(
            file_get_contents(__DIR__ . '/../TestAsset/FooBarClass.php')
        ));
        $uses = $tokenScanner->getUses();
        self::assertInternalType('array', $uses);
        $foundUses = [];
        foreach ($uses as $use) {
            $foundUses[] = $use['use'];
        }
        self::assertContains('ArrayObject', $foundUses);
    }

    public function testScannerReturnsClassNames()
    {
        $tokenScanner = new TokenArrayScanner(token_get_all(
            file_get_contents(__DIR__ . '/../TestAsset/FooClass.php')
        ));
        $classes = $tokenScanner->getClassNames();
        self::assertInternalType('array', $classes);
        self::assertContains(FooClass::class, $classes);
    }

    /**
     * @group gh-4989
     */
    public function testScannerReturnsClassNamesForTraits()
    {
        $tokenScanner = new TokenArrayScanner(token_get_all(
            file_get_contents(__DIR__ . '/../TestAsset/FooTrait.php')
        ));
        $classes = $tokenScanner->getClassNames();
        self::assertInternalType('array', $classes);
        self::assertContains(FooTrait::class, $classes);
    }

    public function testScannerReturnsFunctions()
    {
        $tokenScanner = new TokenArrayScanner(token_get_all(
            file_get_contents(__DIR__ . '/../TestAsset/functions.php')
        ));
        $functions = $tokenScanner->getFunctionNames();
        self::assertInternalType('array', $functions);
        self::assertContains('ZendTest\Code\TestAsset\foo_bar', $functions);
    }

    public function testScannerReturnsClassScanner()
    {
        $tokenScanner = new TokenArrayScanner(token_get_all(
            file_get_contents(__DIR__ . '/../TestAsset/FooClass.php')
        ));
        $classes = $tokenScanner->getClasses();
        self::assertInternalType('array', $classes);
        foreach ($classes as $class) {
            self::assertInstanceOf(ClassScanner::class, $class);
        }
    }

    public function testScannerCanHandleMultipleNamespaceFile()
    {
        $tokenScanner = new TokenArrayScanner(token_get_all(
            file_get_contents(__DIR__ . '/../TestAsset/MultipleNamespaces.php')
        ));
        self::assertEquals(Baz::class, $tokenScanner->getClass(Baz::class)->getName());
        self::assertEquals('Foo', $tokenScanner->getClass('Foo')->getName());
    }
}
