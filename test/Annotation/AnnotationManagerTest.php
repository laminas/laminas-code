<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Annotation;

use Laminas\Code\Annotation;
use Laminas\Code\Reflection;
use PHPUnit_Framework_TestCase as TestCase;

class AnnotationManagerTest extends TestCase
{
    public function setUp()
    {
        if (!defined('TESTS_LAMINAS_CODE_ANNOTATION_DOCTRINE_SUPPORT')
            || !constant('TESTS_LAMINAS_CODE_ANNOTATION_DOCTRINE_SUPPORT')
        ) {
            $this->markTestSkipped('Enable TESTS_LAMINAS_CODE_ANNOTATION_DOCTRINE_SUPPORT to test doctrine annotation parsing');
        }

        $this->manager = new Annotation\AnnotationManager();
    }

    public function testAllowsMultipleParsingStrategies()
    {
        $genericParser = new Annotation\Parser\GenericAnnotationParser();
        $genericParser->registerAnnotation(__NAMESPACE__ . '\TestAsset\Foo');

        $doctrineParser = new Annotation\Parser\DoctrineAnnotationParser();
        $doctrineParser->registerAnnotation(__NAMESPACE__ . '\TestAsset\DoctrineAnnotation');

        $this->manager->attach($genericParser);
        $this->manager->attach($doctrineParser);

        $reflection = new Reflection\ClassReflection(__NAMESPACE__ . '\TestAsset\EntityWithMixedAnnotations');
        $prop = $reflection->getProperty('test');
        $annotations = $prop->getAnnotations($this->manager);

        $this->assertTrue($annotations->hasAnnotation(__NAMESPACE__ . '\TestAsset\Foo'));
        $this->assertTrue($annotations->hasAnnotation(__NAMESPACE__ . '\TestAsset\DoctrineAnnotation'));
        $this->assertFalse($annotations->hasAnnotation(__NAMESPACE__ . '\TestAsset\Bar'));

        foreach ($annotations as $annotation) {
            switch (get_class($annotation)) {
                case __NAMESPACE__ . '\TestAsset\Foo':
                    $this->assertEquals('first', $annotation->content);
                    break;
                case __NAMESPACE__ . '\TestAsset\DoctrineAnnotation':
                    $this->assertEquals(array('foo' => 'bar', 'bar' => 'baz'), $annotation->value);
                    break;
                default:
                    $this->fail('Received unexpected annotation "' . get_class($annotation) . '"');
            }
        }
    }
}
