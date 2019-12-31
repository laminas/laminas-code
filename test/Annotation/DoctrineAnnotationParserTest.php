<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Annotation;

use Laminas\Code\Annotation;
use Laminas\Code\Annotation\Parser\DoctrineAnnotationParser;
use Laminas\EventManager\Event;
use PHPUnit_Framework_TestCase as TestCase;

class DoctrineAnnotationParserTest extends TestCase
{
    /**
     * @var DoctrineAnnotationParser
     */
    private $parser;

    public function setUp()
    {
        if (!getenv('TESTS_LAMINAS_CODE_ANNOTATION_DOCTRINE_SUPPORT')) {
            $this->markTestSkipped('Enable TESTS_LAMINAS_CODE_ANNOTATION_DOCTRINE_SUPPORT to test doctrine annotation parsing');
        }

        $this->parser = new DoctrineAnnotationParser();
    }

    public function getEvent()
    {
        $event = new Event();
        $event->setParams([
            'class'   => __NAMESPACE__ . '\TestAsset\DoctrineAnnotation',
            'content' => '(foo="bar")',
            'raw'     => '@' . __NAMESPACE__ . '\TestAsset\DoctrineAnnotation(foo="bar")',
        ]);
        return $event;
    }

    public function testParserCreatesNewAnnotationInstances()
    {
        $this->parser->registerAnnotation(__NAMESPACE__ . '\TestAsset\DoctrineAnnotation');

        $event = $this->getEvent();
        $test  = $this->parser->onCreateAnnotation($event);
        $this->assertInstanceOf(__NAMESPACE__ . '\TestAsset\DoctrineAnnotation', $test);
        $this->assertEquals(['foo' => 'bar'], $test->value);
    }

    public function testReturnsFalseDuringCreationIfAnnotationIsNotRegistered()
    {
        $event = $this->getEvent();
        $this->assertFalse($this->parser->onCreateAnnotation($event));
    }

    public function testReturnsFalseClassNotSet()
    {
        $this->assertFalse($this->parser->onCreateAnnotation(new Event()));
    }

    public function testReturnsFalseRawNotSet()
    {
        $this->parser->registerAnnotation(__NAMESPACE__ . '\TestAsset\DoctrineAnnotation');
        $event = $this->getEvent();
        $event->setParam('raw', false);

        $this->assertFalse($this->parser->onCreateAnnotation($event));
    }

    public function testReturnsFalseEmptyAnnotations()
    {
        $this->parser->registerAnnotation(__NAMESPACE__ . '\TestAsset\DoctrineAnnotation');
        $event = $this->getEvent();
        $event->setParam('raw', 'foo');
        $this->assertFalse($this->parser->onCreateAnnotation($event));
    }

    /**
     * @expectedException \Laminas\Code\Exception\InvalidArgumentException
     */
    public function testRegisterAnnotationsThrowsException()
    {
        $this->parser->registerAnnotations('some string');
    }

    public function testRegisterAnnotations()
    {
        $this->parser->registerAnnotations([__NAMESPACE__ . '\TestAsset\DoctrineAnnotation']);
        $event = $this->getEvent();
        $test  = $this->parser->onCreateAnnotation($event);
        $this->assertInstanceOf(__NAMESPACE__ . '\TestAsset\DoctrineAnnotation', $test);
        $this->assertEquals(['foo' => 'bar'], $test->value);
    }
}
