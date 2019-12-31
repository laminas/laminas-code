<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Annotation;

use Laminas\Code\Annotation;
use Laminas\Code\Exception\InvalidArgumentException;
use Laminas\EventManager\Event;
use PHPUnit\Framework\TestCase;

use function get_class;

class GenericAnnotationParserTest extends TestCase
{
    /**
     * @var Annotation\Parser\GenericAnnotationParser
     */
    private $parser;

    protected function setUp() : void
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
