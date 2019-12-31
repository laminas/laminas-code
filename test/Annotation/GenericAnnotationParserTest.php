<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Annotation;

use Laminas\Code\Annotation;
use Laminas\EventManager\Event;
use PHPUnit_Framework_TestCase as TestCase;

class GenericAnnotationParserTest extends TestCase
{
    /**
     * @var Annotation\Parser\GenericAnnotationParser
     */
    private $parser;

    public function setUp()
    {
        $this->parser = new Annotation\Parser\GenericAnnotationParser();
    }

    public function getFooEvent()
    {
        $event = new Event();
        $event->setParams(array(
            'class' => __NAMESPACE__ . '\TestAsset\Foo',
            'content' => '(test content)',
            'raw' => '@' . __NAMESPACE__ . '\TestAsset\Foo(test content)',
        ));
        return $event;
    }

    public function testParserKeepsTrackOfAllowedAnnotations()
    {
        $this->parser->registerAnnotation(new TestAsset\Foo());
        $this->parser->registerAnnotation(new TestAsset\Bar());

        $this->assertTrue($this->parser->hasAnnotation(__NAMESPACE__ . '\TestAsset\Foo'));
        $this->assertTrue($this->parser->hasAnnotation(__NAMESPACE__ . '\TestAsset\Bar'));
        $this->assertFalse($this->parser->hasAnnotation(__NAMESPACE__ . '\TestAsset\Bogus'));
    }

    public function testParserCreatesNewAnnotationInstances()
    {
        $foo = new TestAsset\Foo();
        $this->parser->registerAnnotation($foo);

        $event = $this->getFooEvent();
        $test = $this->parser->onCreateAnnotation($event);
        $this->assertInstanceOf(__NAMESPACE__ . '\TestAsset\Foo', $test);
        $this->assertNotSame($foo, $test);
        $this->assertEquals('test content', $test->content);
    }

    public function testReturnsFalseDuringCreationIfAnnotationIsNotRegistered()
    {
        $event = $this->getFooEvent();
        $this->assertFalse($this->parser->onCreateAnnotation($event));
    }

    public function testParserAllowsPassingArrayOfAnnotationInstances()
    {
        $this->parser->registerAnnotations(array(
            new TestAsset\Foo(),
            new TestAsset\Bar(),
        ));
        $this->assertTrue($this->parser->hasAnnotation(__NAMESPACE__ . '\TestAsset\Foo'));
        $this->assertTrue($this->parser->hasAnnotation(__NAMESPACE__ . '\TestAsset\Bar'));
    }

    public function testAllowsSpecifyingAliases()
    {
        $bar = new TestAsset\Bar();
        $this->parser->registerAnnotation($bar);
        $this->parser->setAlias(__NAMESPACE__ . '\TestAsset\Foo', get_class($bar));

        $event = $this->getFooEvent();
        $test  = $this->parser->onCreateAnnotation($event);
        $this->assertInstanceOf(__NAMESPACE__ . '\TestAsset\Bar', $test);
        $this->assertNotSame($bar, $test);
        $this->assertEquals('test content', $test->content);
    }

    /**
     * @expectedException \Laminas\Code\Exception\InvalidArgumentException
     */
    public function testRegisterAnnotationAllowsAnnotationInterfaceOnly()
    {
        $this->parser->registerAnnotation(new \stdClass());
    }

    /**
     * @expectedException \Laminas\Code\Exception\InvalidArgumentException
     */
    public function testAllowRegistrationOnceOnly()
    {
        $bar = new TestAsset\Bar();
        $this->parser->registerAnnotation($bar);
        $this->parser->registerAnnotation($bar);
    }

    public function testRegisterAnnotations()
    {
        $this->parser->registerAnnotations(array(new TestAsset\Foo));
        $event = $this->getFooEvent();
        $test  = $this->parser->onCreateAnnotation($event);
        $this->assertInstanceOf(__NAMESPACE__ . '\TestAsset\Foo', $test);
    }

    /**
     * @expectedException \Laminas\Code\Exception\InvalidArgumentException
     */
    public function testRegisterAnnotationsThrowsException()
    {
        $this->parser->registerAnnotations('some string');
    }

    /**
     * @expectedException \Laminas\Code\Exception\InvalidArgumentException
     */
    public function testSetAliasNotRegisteredClassThrowsException()
    {
        $this->parser->setAlias('bar', 'foo');
    }
}
