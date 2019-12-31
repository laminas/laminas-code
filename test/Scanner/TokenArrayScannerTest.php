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
        $namespaces = $tokenScanner->getNamespaces();
        $this->assertInternalType('array', $namespaces);
        $this->assertContains('LaminasTest\Code\TestAsset', $namespaces);
    }

    public function testScannerReturnsClassNames()
    {
        $tokenScanner = new TokenArrayScanner(token_get_all(file_get_contents((__DIR__ . '/../TestAsset/FooClass.php'))));
        $classes = $tokenScanner->getClassNames();
        $this->assertInternalType('array', $classes);
        $this->assertContains('LaminasTest\Code\TestAsset\FooClass', $classes);
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





