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
use Zend\Code\Reflection;

use function get_class;
use function getenv;

class AnnotationManagerTest extends TestCase
{
    public function setUp()
    {
        if (! getenv('TESTS_ZEND_CODE_ANNOTATION_DOCTRINE_SUPPORT')) {
            $this->markTestSkipped(
                'Enable TESTS_ZEND_CODE_ANNOTATION_DOCTRINE_SUPPORT to test doctrine annotation parsing'
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
