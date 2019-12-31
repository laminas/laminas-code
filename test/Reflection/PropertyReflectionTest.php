<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection;

use Laminas\Code\Annotation\AnnotationCollection;
use Laminas\Code\Annotation\AnnotationManager;
use Laminas\Code\Annotation\Parser\GenericAnnotationParser;
use Laminas\Code\Reflection\ClassReflection;
use Laminas\Code\Reflection\PropertyReflection;
use Laminas\Code\Scanner\CachingFileScanner;
use LaminasTest\Code\Reflection\TestAsset\InjectablePropertyReflection;
use PHPUnit\Framework\TestCase;

use function get_class;

/**
 * @group      Laminas_Reflection
 * @group      Laminas_Reflection_Property
 */
class PropertyReflectionTest extends TestCase
{
    public function testDeclaringClassReturn()
    {
        $property = new PropertyReflection(TestAsset\TestSampleClass2::class, '_prop1');
        self::assertInstanceOf(ClassReflection::class, $property->getDeclaringClass());
        self::assertEquals(TestAsset\TestSampleClass2::class, $property->getDeclaringClass()->getName());
    }

    public function testAnnotationScanningIsPossible()
    {
        $manager = new AnnotationManager();
        $parser = new GenericAnnotationParser();
        $parser->registerAnnotation(new TestAsset\SampleAnnotation());
        $manager->attach($parser);

        $property = new PropertyReflection(TestAsset\TestSampleClass2::class, '_prop2');
        $annotations = $property->getAnnotations($manager);
        self::assertInstanceOf(AnnotationCollection::class, $annotations);
        self::assertTrue($annotations->hasAnnotation(TestAsset\SampleAnnotation::class));
        $found = false;
        foreach ($annotations as $key => $annotation) {
            if (! $annotation instanceof TestAsset\SampleAnnotation) {
                continue;
            }
            self::assertEquals(get_class($annotation) . ': {"foo":"bar"}', $annotation->content);
            $found = true;
            break;
        }
        self::assertTrue($found);
    }

    public function testGetAnnotationsWithNoNameInformations()
    {
        $reflectionProperty = new InjectablePropertyReflection(
            // TestSampleClass5 has the annotations required to get to the
            // right point in the getAnnotations method.
            TestAsset\TestSampleClass2::class,
            '_prop2'
        );

        $annotationManager = new AnnotationManager();

        $fileScanner = $this->getMockBuilder(CachingFileScanner::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $reflectionProperty->setFileScanner($fileScanner);

        $fileScanner->method('getClassNameInformation')
                    ->willReturn(false);

        self::assertFalse($reflectionProperty->getAnnotations($annotationManager));
    }
}
