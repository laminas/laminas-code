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
use Zend\Code\Annotation;
use Zend\Code\Exception\RuntimeException;
use Zend\Code\Scanner\ConstantScanner;
use Zend\Code\Scanner\FileScanner;
use Zend\Code\Scanner\MethodScanner;
use Zend\Code\Scanner\PropertyScanner;
use Zend\Stdlib\ErrorHandler;
use ZendTest\Code\Annotation\TestAsset\Bar;
use ZendTest\Code\Annotation\TestAsset\EntityWithAnnotations;
use ZendTest\Code\Annotation\TestAsset\Foo;
use ZendTest\Code\TestAsset\BarClass;
use ZendTest\Code\TestAsset\BarTrait;
use ZendTest\Code\TestAsset\BazTrait;
use ZendTest\Code\TestAsset\FooClass;
use ZendTest\Code\TestAsset\FooInterface;
use ZendTest\Code\TestAsset\FooTrait;
use ZendTest\Code\TestAsset\TestClassUsesTraitSimple;
use ZendTest\Code\TestAsset\TestClassWithTraitAliases;

class ClassScannerTest extends TestCase
{
    protected $manager;

    public function setUp()
    {
        $this->manager = new Annotation\AnnotationManager();

        $genericParser = new Annotation\Parser\GenericAnnotationParser();
        $genericParser->registerAnnotation(Foo::class);
        $genericParser->registerAnnotation(Bar::class);

        $this->manager->attach($genericParser);
    }

    public function testClassScannerHasClassInformation()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class = $file->getClass(FooClass::class);
        $this->assertEquals(FooClass::class, $class->getName());
        $this->assertEquals('FooClass', $class->getShortName());
        $this->assertFalse($class->isFinal());
        $this->assertTrue($class->isAbstract());
        $this->assertFalse($class->isInterface());
        $interfaces = $class->getInterfaces();
        $this->assertContains('ArrayAccess', $interfaces);
        $this->assertContains('A\B\C\D\Blarg', $interfaces);
        $this->assertContains('ZendTest\Code\TestAsset\Local\SubClass', $interfaces);
        $methods = $class->getMethodNames();
        $this->assertInternalType('array', $methods);
        $this->assertContains('fooBarBaz', $methods);
    }

    public function testClassScannerHasConstant()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class = $file->getClass(FooClass::class);
        $this->assertInternalType('array', $class->getConstantNames());
        $this->assertContains('FOO', $class->getConstantNames());
    }

    public function testClassScannerHasProperties()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class = $file->getClass(FooClass::class);
        $this->assertContains('bar', $class->getPropertyNames());
    }

    public function testClassScannerHasMethods()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class = $file->getClass(FooClass::class);
        $this->assertContains('fooBarBaz', $class->getMethodNames());
    }

    /**
     * @todo Remove error handling once we remove deprecation warning from getConstants method
     */
    public function testGetConstantsReturnsConstantNames()
    {
        $file      = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class     = $file->getClass(FooClass::class);

        ErrorHandler::start(E_USER_DEPRECATED);
        $constants = $class->getConstants();
        $error = ErrorHandler::stop();

        $this->assertInstanceOf(\ErrorException::class, $error);
        $this->assertContains('FOO', $constants);
    }

    public function testGetConstantsReturnsInstancesOfConstantScanner()
    {
        $file    = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class   = $file->getClass(FooClass::class);
        $constants = $class->getConstants(false);
        foreach ($constants as $constant) {
            $this->assertInstanceOf(ConstantScanner::class, $constant);
        }
    }

    public function testHasConstant()
    {
        $file    = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class   = $file->getClass(FooClass::class);
        $this->assertTrue($class->hasConstant('FOO'));
        $this->assertFalse($class->hasConstant('foo'));
    }

    public function testHasProperty()
    {
        $file    = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class   = $file->getClass(FooClass::class);
        $this->assertTrue($class->hasProperty('foo'));
        $this->assertFalse($class->hasProperty('FOO'));
        $this->assertTrue($class->hasProperty('bar'));
    }

    public function testHasMethod()
    {
        $file    = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class   = $file->getClass(FooClass::class);
        $this->assertTrue($class->hasMethod('fooBarBaz'));
        $this->assertFalse($class->hasMethod('FooBarBaz'));
        $this->assertFalse($class->hasMethod('bar'));
    }

    public function testClassScannerReturnsMethodsWithMethodScanners()
    {
        $file    = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class   = $file->getClass(FooClass::class);
        $methods = $class->getMethods();
        foreach ($methods as $method) {
            $this->assertInstanceOf(MethodScanner::class, $method);
        }
    }

    public function testClassScannerReturnsPropertiesWithPropertyScanners()
    {
        $file    = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class   = $file->getClass(FooClass::class);
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $this->assertInstanceOf(PropertyScanner::class, $property);
        }
    }

    public function testClassScannerCanScanInterface()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooInterface.php');
        $class = $file->getClass(FooInterface::class);
        $this->assertEquals(FooInterface::class, $class->getName());
    }

    public function testClassScannerCanReturnLineNumbers()
    {
        $file    = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class   = $file->getClass(FooClass::class);
        $this->assertEquals(11, $class->getLineStart());
        $this->assertEquals(36, $class->getLineEnd());

        $file    = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class   = $file->getClass(BarClass::class);
        $this->assertEquals(10, $class->getLineStart());
        $this->assertEquals(42, $class->getLineEnd());
    }

    public function testClassScannerCanScanAnnotations()
    {
        $file    = new FileScanner(__DIR__ . '/../Annotation/TestAsset/EntityWithAnnotations.php');
        $class   = $file->getClass(EntityWithAnnotations::class);
        $annotations = $class->getAnnotations($this->manager);

        $this->assertTrue($annotations->hasAnnotation(Foo::class));
        $this->assertTrue($annotations->hasAnnotation(Bar::class));

        $this->assertEquals('first', $annotations[0]->content);
        $this->assertEquals('second', $annotations[1]->content);
        $this->assertEquals('third', $annotations[2]->content);
    }

    /**
     * @group trait1
     */
    public function testClassScannerCanScanTraits()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/BarTrait.php');
        $class = $file->getClass(BarTrait::class);

        $this->assertTrue($class->isTrait());
        $this->assertTrue($class->hasMethod('bar'));
    }

    /**
     * @group trait2
     */
    public function testClassScannerCanScanClassThatUsesTraits()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/TestClassUsesTraitSimple.php');
        $class = $file->getClass(TestClassUsesTraitSimple::class);

        $this->assertFalse($class->isTrait());
        $traitNames = $class->getTraitNames();
        $class->getTraitAliases();
        $this->assertContains(BarTrait::class, $traitNames);
        $this->assertContains(FooTrait::class, $traitNames);
        $this->assertContains(BazTrait::class, $traitNames);
    }

    /**
     * @group trait3
     */
    public function testClassScannerCanScanClassAndGetTraitsAliases()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/TestClassWithTraitAliases.php');
        $class = $file->getClass(TestClassWithTraitAliases::class);

        $this->assertFalse($class->isTrait());

        $aliases = $class->getTraitAliases();

        $this->assertCount(1, $aliases);

        $this->assertEquals(key($aliases), 'test');
        $this->assertEquals(current($aliases), 'ZendTest\Code\TestAsset\TraitWithSameMethods::foo');
    }

    /**
     * @group trait4
     */
    public function testClassScannerCanGetTraitMethodsInGetMethods()
    {
        //load files or test may fail due to autoload issues
        require_once __DIR__ . '/../TestAsset/TraitWithSameMethods.php';
        require_once __DIR__ . '/../TestAsset/BarTrait.php';

        $file  = new FileScanner(__DIR__ . '/../TestAsset/TestClassWithTraitAliases.php');

        $class = $file->getClass(TestClassWithTraitAliases::class);

        $this->assertFalse($class->isTrait());

        $testMethods = [
            'fooBarBaz' => 'isPublic',
            'foo' => 'isPublic',
            'bar' => 'isPublic',
            'test' => 'isPrivate',
            'bazFooBar' => 'isPublic',
        ];

        $this->assertEquals($class->getMethodNames(), array_keys($testMethods));

        foreach ($testMethods as $methodName => $testMethod) {
            $this->assertTrue($class->hasMethod($methodName), "Cannot find method $methodName");

            $method = $class->getMethod($methodName);
            $this->assertInstanceOf(MethodScanner::class, $method, $methodName . ' not found.');

            $this->assertTrue($method->$testMethod());

            // test that we got the right ::bar method based on declaration
            if ($testMethod === 'bar') {
                $this->assertEquals(trim($method->getBody), 'echo "foo";');
            }
        }
    }

    /**
     * @group trait5
     */
    public function testGetMethodsThrowsExceptionOnDuplicateMethods()
    {
        $file  = new FileScanner(__DIR__ . '/TestAsset/TestClassWithAliasException.php');
        $class = $file->getClass(TestAsset\TestClassWithAliasException::class);

        $this->expectException(RuntimeException::class);
        $class->getMethods();
    }

    public function testClassIsInstantiable()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooBarClass.php');
        $class = $file->getClass('ZendTest_Code_TestAsset_FooBar');
        $this->assertFalse($class->isAbstract());
        $this->assertTrue($class->isInstantiable());
    }

    public function testAbstractClassIsNotInstantiable()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class = $file->getClass(FooClass::class);
        $this->assertTrue($class->isAbstract());
        $this->assertFalse($class->isInstantiable());
    }

    public function testInterfaceIsNotInstantiable()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooInterface.php');
        $class = $file->getClass(FooInterface::class);
        $this->assertTrue($class->isInterface());
        $this->assertFalse($class->isInstantiable());
    }

    public function testTraitIsNotInstantiable()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooTrait.php');
        $class = $file->getClass(FooTrait::class);
        $this->assertTrue($class->isTrait());
        $this->assertFalse($class->isInstantiable());
    }

    public function testGetInterfacesFromInterface()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooInterface.php');
        $class = $file->getClass(FooInterface::class);
        $this->assertTrue($class->isInterface());
        $this->assertCount(1, $class->getInterfaces());
        $this->assertEquals('ArrayAccess', $class->getInterfaces()[0]);
    }
}
