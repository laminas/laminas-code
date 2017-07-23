<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Reflection;

use PHPUnit\Framework\TestCase;
use Zend\Code\Annotation\AnnotationCollection;
use Zend\Code\Annotation\AnnotationManager;
use Zend\Code\Annotation\Parser\GenericAnnotationParser;
use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Reflection\PropertyReflection;
use Zend\Code\Scanner\CachingFileScanner;
use ZendTest\Code\Reflection\TestAsset\InjectablePropertyReflection;

use function get_class;

/**
 * @group      Zend_Reflection
 * @group      Zend_Reflection_Property
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

        $fileScanner->expects($this->any())
                    ->method('getClassNameInformation')
                    ->will($this->returnValue(false));

        self::assertFalse($reflectionProperty->getAnnotations($annotationManager));
    }
}
