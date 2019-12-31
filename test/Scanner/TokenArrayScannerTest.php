<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Scanner;

use Laminas\Code\Scanner\TokenArrayScanner;
use PHPUnit_Framework_TestCase as TestCase;

class TokenArrayScannerTest extends TestCase
{

    public function testScannerReturnsNamespaces()
    {
        $tokenScanner = new TokenArrayScanner(token_get_all(file_get_contents((__DIR__ . '/../TestAsset/FooClass.php'))));
        $this->assertTrue($tokenScanner->hasNamespace('LaminasTest\Code\TestAsset'));
        $namespaces = $tokenScanner->getNamespaces();
        $this->assertInternalType('array', $namespaces);
        $this->assertContains('LaminasTest\Code\TestAsset', $namespaces);
    }

    public function testScannerReturnsNamespacesInNotNamespacedClasses()
    {
        $tokenScanner = new TokenArrayScanner(token_get_all(file_get_contents((__DIR__ . '/../TestAsset/FooBarClass.php'))));
        $uses = $tokenScanner->getUses();
        $this->assertInternalType('array', $uses);
        $foundUses = array();
        foreach ($uses as $use) {
            $foundUses[] = $use['use'];
        }
        $this->assertContains('ArrayObject', $foundUses);
    }

    public function testScannerReturnsClassNames()
    {
        $tokenScanner = new TokenArrayScanner(token_get_all(file_get_contents((__DIR__ . '/../TestAsset/FooClass.php'))));
        $classes = $tokenScanner->getClassNames();
        $this->assertInternalType('array', $classes);
        $this->assertContains('LaminasTest\Code\TestAsset\FooClass', $classes);
    }

    /**
     * @group gh-4989
     */
    public function testScannerReturnsClassNamesForTraits()
    {
        if (version_compare(PHP_VERSION, '5.4', 'lt')) {
            $this->markTestSkipped('Skipping; PHP 5.4 or greater is needed');
        }
        $tokenScanner = new TokenArrayScanner(token_get_all(file_get_contents((__DIR__ . '/../TestAsset/FooTrait.php'))));
        $classes = $tokenScanner->getClassNames();
        $this->assertInternalType('array', $classes);
        $this->assertContains('LaminasTest\Code\TestAsset\FooTrait', $classes);
    }

    public function testScannerReturnsFunctions()
    {
        $tokenScanner = new TokenArrayScanner(token_get_all(file_get_contents((__DIR__ . '/../TestAsset/functions.php'))));
        $functions = $tokenScanner->getFunctionNames();
        $this->assertInternalType('array', $functions);
        $this->assertContains('LaminasTest\Code\TestAsset\foo_bar', $functions);
    }

    public function testScannerReturnsClassScanner()
    {
        $tokenScanner = new TokenArrayScanner(token_get_all(file_get_contents((__DIR__ . '/../TestAsset/FooClass.php'))));
        $classes = $tokenScanner->getClasses(true);
        $this->assertInternalType('array', $classes);
        foreach ($classes as $class) {
            $this->assertInstanceOf('Laminas\Code\Scanner\ClassScanner', $class);
        }
    }

    public function testScannerCanHandleMultipleNamespaceFile()
    {
        $tokenScanner = new TokenArrayScanner(token_get_all(file_get_contents((__DIR__ . '/../TestAsset/MultipleNamespaces.php'))));
        $this->assertEquals('LaminasTest\Code\TestAsset\Baz', $tokenScanner->getClass('LaminasTest\Code\TestAsset\Baz')->getName());
        $this->assertEquals('Foo', $tokenScanner->getClass('Foo')->getName());
    }

}
