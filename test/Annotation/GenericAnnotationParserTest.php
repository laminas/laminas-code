<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Annotation;

use PHPUnit\Framework\TestCase;
use Zend\Code\Annotation;
use Zend\Code\Exception\InvalidArgumentException;
use Zend\EventManager\Event;

use function get_class;

class GenericAnnotationParserTest extends TestCase
{
    /**
     * @var Annotation\Parser\GenericAnnotationParser
     */
    private $parser;

    public function setUp(): void
    {
        $this->parser = new Annotation\Parser\GenericAnnotationParser();
    }

    public function getFooEvent()
    {
        $event = new Event();
        $event->setParams([
            'class' => __NAMESPACE__ . '\TestAsset\Foo',
            'content' => '(test content)',
            'raw' => '@' . __NAMESPACE__ . '\TestAsset\Foo(test content)',
        ]);
        return $event;
    }

    public function testParserKeepsTrackOfAllowedAnnotations()
    {
        $this->parser->registerAnnotation(new TestAsset\Foo());
        $this->parser->registerAnnotation(new TestAsset\Bar());

        self::assertTrue($this->parser->hasAnnotation(__NAMESPACE__ . '\TestAsset\Foo'));
        self::assertTrue($this->parser->hasAnnotation(__NAMESPACE__ . '\TestAsset\Bar'));
        self::assertFalse($this->parser->hasAnnotation(__NAMESPACE__ . '\TestAsset\Bogus'));
    }

    public function testParserCreatesNewAnnotationInstances()
    {
        $foo = new TestAsset\Foo();
        $this->parser->registerAnnotation($foo);

        $event = $this->getFooEvent();
        $test = $this->parser->onCreateAnnotation($event);
        self::assertInstanceOf(__NAMESPACE__ . '\TestAsset\Foo', $test);
        self::assertNotSame($foo, $test);
        self::assertEquals('test content', $test->content);
    }

    public function testReturnsFalseDuringCreationIfAnnotationIsNotRegistered()
    {
        $event = $this->getFooEvent();
        self::assertFalse($this->parser->onCreateAnnotation($event));
    }

    public function testParserAllowsPassingArrayOfAnnotationInstances()
    {
        $this->parser->registerAnnotations([
            new TestAsset\Foo(),
            new TestAsset\Bar(),
        ]);
        self::assertTrue($this->parser->hasAnnotation(__NAMESPACE__ . '\TestAsset\Foo'));
        self::assertTrue($this->parser->hasAnnotation(__NAMESPACE__ . '\TestAsset\Bar'));
    }

    public function testAllowsSpecifyingAliases()
    {
        $bar = new TestAsset\Bar();
        $this->parser->registerAnnotation($bar);
        $this->parser->setAlias(__NAMESPACE__ . '\TestAsset\Foo', get_class($bar));

        $event = $this->getFooEvent();
        $test  = $this->parser->onCreateAnnotation($event);
        self::assertInstanceOf(__NAMESPACE__ . '\TestAsset\Bar', $test);
        self::assertNotSame($bar, $test);
        self::assertEquals('test content', $test->content);
    }

    public function testRegisterAnnotationAllowsAnnotationInterfaceOnly()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->parser->registerAnnotation(new \stdClass());
    }

    public function testAllowRegistrationOnceOnly()
    {
        $bar = new TestAsset\Bar();
        $this->parser->registerAnnotation($bar);

        $this->expectException(InvalidArgumentException::class);
        $this->parser->registerAnnotation($bar);
    }

    public function testRegisterAnnotations()
    {
        $this->parser->registerAnnotations([new TestAsset\Foo()]);
        $event = $this->getFooEvent();
        $test  = $this->parser->onCreateAnnotation($event);
        self::assertInstanceOf(__NAMESPACE__ . '\TestAsset\Foo', $test);
    }

    public function testRegisterAnnotationsThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->parser->registerAnnotations('some string');
    }

    public function testSetAliasNotRegisteredClassThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->parser->setAlias('bar', 'foo');
    }
}
