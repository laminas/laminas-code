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

use function array_keys;
use function current;
use function key;
use function trim;

class ClassScannerTest extends TestCase
{
    protected $manager;

    public function setUp(): void
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
        self::assertEquals(FooClass::class, $class->getName());
        self::assertEquals('FooClass', $class->getShortName());
        self::assertFalse($class->isFinal());
        self::assertTrue($class->isAbstract());
        self::assertFalse($class->isInterface());
        $interfaces = $class->getInterfaces();
        self::assertContains('ArrayAccess', $interfaces);
        self::assertContains('A\B\C\D\Blarg', $interfaces);
        self::assertContains('ZendTest\Code\TestAsset\Local\SubClass', $interfaces);
        $methods = $class->getMethodNames();
        self::assertInternalType('array', $methods);
        self::assertContains('fooBarBaz', $methods);
    }

    public function testClassScannerHasConstant()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class = $file->getClass(FooClass::class);
        self::assertInternalType('array', $class->getConstantNames());
        self::assertContains('FOO', $class->getConstantNames());
    }

    public function testClassScannerHasProperties()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class = $file->getClass(FooClass::class);
        self::assertContains('bar', $class->getPropertyNames());
    }

    public function testClassScannerHasMethods()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class = $file->getClass(FooClass::class);
        self::assertContains('fooBarBaz', $class->getMethodNames());
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

        self::assertInstanceOf(\ErrorException::class, $error);
        self::assertContains('FOO', $constants);
    }

    public function testGetConstantsReturnsInstancesOfConstantScanner()
    {
        $file    = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class   = $file->getClass(FooClass::class);
        $constants = $class->getConstants(false);
        foreach ($constants as $constant) {
            self::assertInstanceOf(ConstantScanner::class, $constant);
        }
    }

    public function testHasConstant()
    {
        $file    = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class   = $file->getClass(FooClass::class);
        self::assertTrue($class->hasConstant('FOO'));
        self::assertFalse($class->hasConstant('foo'));
    }

    public function testHasProperty()
    {
        $file    = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class   = $file->getClass(FooClass::class);
        self::assertTrue($class->hasProperty('foo'));
        self::assertFalse($class->hasProperty('FOO'));
        self::assertTrue($class->hasProperty('bar'));
    }

    public function testHasMethod()
    {
        $file    = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class   = $file->getClass(FooClass::class);
        self::assertTrue($class->hasMethod('fooBarBaz'));
        self::assertFalse($class->hasMethod('FooBarBaz'));
        self::assertFalse($class->hasMethod('bar'));
    }

    public function testClassScannerReturnsMethodsWithMethodScanners()
    {
        $file    = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class   = $file->getClass(FooClass::class);
        $methods = $class->getMethods();
        foreach ($methods as $method) {
            self::assertInstanceOf(MethodScanner::class, $method);
        }
    }

    public function testClassScannerReturnsPropertiesWithPropertyScanners()
    {
        $file    = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class   = $file->getClass(FooClass::class);
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            self::assertInstanceOf(PropertyScanner::class, $property);
        }
    }

    public function testClassScannerCanScanInterface()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooInterface.php');
        $class = $file->getClass(FooInterface::class);
        self::assertEquals(FooInterface::class, $class->getName());
    }

    public function testClassScannerCanReturnLineNumbers()
    {
        $file    = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class   = $file->getClass(FooClass::class);
        self::assertEquals(11, $class->getLineStart());
        self::assertEquals(36, $class->getLineEnd());

        $file    = new FileScanner(__DIR__ . '/../TestAsset/BarClass.php');
        $class   = $file->getClass(BarClass::class);
        self::assertEquals(10, $class->getLineStart());
        self::assertEquals(42, $class->getLineEnd());
    }

    public function testClassScannerCanScanAnnotations()
    {
        $file    = new FileScanner(__DIR__ . '/../Annotation/TestAsset/EntityWithAnnotations.php');
        $class   = $file->getClass(EntityWithAnnotations::class);
        $annotations = $class->getAnnotations($this->manager);

        self::assertTrue($annotations->hasAnnotation(Foo::class));
        self::assertTrue($annotations->hasAnnotation(Bar::class));

        self::assertEquals('first', $annotations[0]->content);
        self::assertEquals('second', $annotations[1]->content);
        self::assertEquals('third', $annotations[2]->content);
    }

    /**
     * @group trait1
     */
    public function testClassScannerCanScanTraits()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/BarTrait.php');
        $class = $file->getClass(BarTrait::class);

        self::assertTrue($class->isTrait());
        self::assertTrue($class->hasMethod('bar'));
    }

    /**
     * @group trait2
     */
    public function testClassScannerCanScanClassThatUsesTraits()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/TestClassUsesTraitSimple.php');
        $class = $file->getClass(TestClassUsesTraitSimple::class);

        self::assertFalse($class->isTrait());
        $traitNames = $class->getTraitNames();
        $class->getTraitAliases();
        self::assertContains(BarTrait::class, $traitNames);
        self::assertContains(FooTrait::class, $traitNames);
        self::assertContains(BazTrait::class, $traitNames);
    }

    /**
     * @group trait3
     */
    public function testClassScannerCanScanClassAndGetTraitsAliases()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/TestClassWithTraitAliases.php');
        $class = $file->getClass(TestClassWithTraitAliases::class);

        self::assertFalse($class->isTrait());

        $aliases = $class->getTraitAliases();

        self::assertCount(1, $aliases);

        self::assertEquals(key($aliases), 'test');
        self::assertEquals(current($aliases), 'ZendTest\Code\TestAsset\TraitWithSameMethods::foo');
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

        self::assertFalse($class->isTrait());

        $testMethods = [
            'fooBarBaz' => 'isPublic',
            'foo' => 'isPublic',
            'bar' => 'isPublic',
            'test' => 'isPrivate',
            'bazFooBar' => 'isPublic',
        ];

        self::assertEquals($class->getMethodNames(), array_keys($testMethods));

        foreach ($testMethods as $methodName => $testMethod) {
            self::assertTrue($class->hasMethod($methodName), sprintf('Cannot find method %s', $methodName));

            $method = $class->getMethod($methodName);
            self::assertInstanceOf(MethodScanner::class, $method, $methodName . ' not found.');

            self::assertTrue($method->$testMethod());

            // test that we got the right ::bar method based on declaration
            if ($testMethod === 'bar') {
                self::assertEquals(trim($method->getBody), 'echo "foo";');
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
        self::assertFalse($class->isAbstract());
        self::assertTrue($class->isInstantiable());
    }

    public function testAbstractClassIsNotInstantiable()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class = $file->getClass(FooClass::class);
        self::assertTrue($class->isAbstract());
        self::assertFalse($class->isInstantiable());
    }

    public function testInterfaceIsNotInstantiable()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooInterface.php');
        $class = $file->getClass(FooInterface::class);
        self::assertTrue($class->isInterface());
        self::assertFalse($class->isInstantiable());
    }

    public function testTraitIsNotInstantiable()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooTrait.php');
        $class = $file->getClass(FooTrait::class);
        self::assertTrue($class->isTrait());
        self::assertFalse($class->isInstantiable());
    }

    public function testGetInterfacesFromInterface()
    {
        $file  = new FileScanner(__DIR__ . '/../TestAsset/FooInterface.php');
        $class = $file->getClass(FooInterface::class);
        self::assertTrue($class->isInterface());
        self::assertCount(1, $class->getInterfaces());
        self::assertEquals('ArrayAccess', $class->getInterfaces()[0]);
    }
}
