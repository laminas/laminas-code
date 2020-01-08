<?php

/**
 * @see       https://github.com/laminasframwork/laminas-code for the canonical source repository
 * @copyright https://github.com/laminasframwork/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminasframwork/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Scanner;

use Laminas\Code\Scanner\ClassScanner;
use Laminas\Code\Scanner\TokenArrayScanner;
use LaminasTest\Code\TestAsset\Baz;
use LaminasTest\Code\TestAsset\FooClass;
use LaminasTest\Code\TestAsset\FooTrait;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function token_get_all;

class TokenArrayScannerTest extends TestCase
{
    public function testScannerReturnsNamespaces()
    {
        $tokenScanner = new TokenArrayScanner(token_get_all(
            file_get_contents(__DIR__ . '/../TestAsset/FooClass.php')
        ));
        self::assertTrue($tokenScanner->hasNamespace('LaminasTest\Code\TestAsset'));
        $namespaces = $tokenScanner->getNamespaces();
        self::assertIsArray($namespaces);
        self::assertContains('LaminasTest\Code\TestAsset', $namespaces);
    }

    public function testScannerReturnsNamespacesInNotNamespacedClasses()
    {
        $tokenScanner = new TokenArrayScanner(token_get_all(
            file_get_contents(__DIR__ . '/../TestAsset/FooBarClass.php')
        ));
        $uses = $tokenScanner->getUses();
        self::assertIsArray($uses);
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
        self::assertIsArray($classes);
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
        self::assertIsArray($classes);
        self::assertContains(FooTrait::class, $classes);
    }

    public function testScannerReturnsFunctions()
    {
        $tokenScanner = new TokenArrayScanner(token_get_all(
            file_get_contents(__DIR__ . '/../TestAsset/functions.php')
        ));
        $functions = $tokenScanner->getFunctionNames();
        self::assertIsArray($functions);
        self::assertContains('LaminasTest\Code\TestAsset\foo_bar', $functions);
    }

    public function testScannerReturnsClassScanner()
    {
        $tokenScanner = new TokenArrayScanner(token_get_all(
            file_get_contents(__DIR__ . '/../TestAsset/FooClass.php')
        ));
        $classes = $tokenScanner->getClasses();
        self::assertIsArray($classes);
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
