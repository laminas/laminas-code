<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Annotation;

use Laminas\Code\Annotation;
use Laminas\Code\Reflection;
use PHPUnit\Framework\TestCase;

use function get_class;
use function getenv;

class AnnotationManagerTest extends TestCase
{
    public function setUp()
    {
        if (! getenv('TESTS_LAMINAS_CODE_ANNOTATION_DOCTRINE_SUPPORT')) {
            $this->markTestSkipped(
                'Enable TESTS_LAMINAS_CODE_ANNOTATION_DOCTRINE_SUPPORT to test doctrine annotation parsing'
            );
        }

        $this->manager = new Annotation\AnnotationManager();
    }

    public function testAllowsMultipleParsingStrategies()
    {
        $genericParser = new Annotation\Parser\GenericAnnotationParser();
        $genericParser->registerAnnotation(TestAsset\Foo::class);
        $doctrineParser = new Annotation\Parser\DoctrineAnnotationParser();
        $doctrineParser->registerAnnotation(TestAsset\DoctrineAnnotation::class);

        $this->manager->attach($genericParser);
        $this->manager->attach($doctrineParser);

        $reflection = new Reflection\ClassReflection(TestAsset\EntityWithMixedAnnotations::class);
        $prop = $reflection->getProperty('test');
        $annotations = $prop->getAnnotations($this->manager);

        self::assertTrue($annotations->hasAnnotation(TestAsset\Foo::class));
        self::assertTrue($annotations->hasAnnotation(TestAsset\DoctrineAnnotation::class));
        self::assertFalse($annotations->hasAnnotation(TestAsset\Bar::class));

        foreach ($annotations as $annotation) {
            switch (get_class($annotation)) {
                case TestAsset\Foo::class:
                    self::assertEquals('first', $annotation->content);
                    break;
                case TestAsset\DoctrineAnnotation::class:
                    self::assertEquals(['foo' => 'bar', 'bar' => 'baz'], $annotation->value);
                    break;
                default:
                    $this->fail('Received unexpected annotation "' . get_class($annotation) . '"');
            }
        }
    }
}
