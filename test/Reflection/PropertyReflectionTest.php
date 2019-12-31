<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection;

use Laminas\Code\Annotation\AnnotationManager;
use Laminas\Code\Annotation\Parser\GenericAnnotationParser;
use LaminasTest\Code\Reflection\TestAsset\InjectablePropertyReflection;

/**
 * @group      Laminas_Reflection
 * @group      Laminas_Reflection_Property
 */
class PropertyReflectionTest extends \PHPUnit_Framework_TestCase
{
    public function testDeclaringClassReturn()
    {
        $property = new \Laminas\Code\Reflection\PropertyReflection('LaminasTest\Code\Reflection\TestAsset\TestSampleClass2', '_prop1');
        $this->assertInstanceOf('Laminas\Code\Reflection\ClassReflection', $property->getDeclaringClass());
        $this->assertEquals('LaminasTest\Code\Reflection\TestAsset\TestSampleClass2', $property->getDeclaringClass()->getName());
    }

    public function testAnnotationScanningIsPossible()
    {
        $manager = new AnnotationManager();
        $parser = new GenericAnnotationParser();
        $parser->registerAnnotation(new TestAsset\SampleAnnotation());
        $manager->attach($parser);

        $property = new \Laminas\Code\Reflection\PropertyReflection('LaminasTest\Code\Reflection\TestAsset\TestSampleClass2', '_prop2');
        $annotations = $property->getAnnotations($manager);
        $this->assertInstanceOf('Laminas\Code\Annotation\AnnotationCollection', $annotations);
        $this->assertTrue($annotations->hasAnnotation('LaminasTest\Code\Reflection\TestAsset\SampleAnnotation'));
        $found = false;
        foreach ($annotations as $key => $annotation) {
            if (!$annotation instanceof TestAsset\SampleAnnotation) {
                continue;
            }
            $this->assertEquals(get_class($annotation) . ': {"foo":"bar"}', $annotation->content);
            $found = true;
            break;
        }
        $this->assertTrue($found);
    }

    public function testGetAnnotationsWithNoNameInformations()
    {
        $reflectionProperty = new InjectablePropertyReflection(
            // TestSampleClass5 has the annotations required to get to the
            // right point in the getAnnotations method.
            'LaminasTest\Code\Reflection\TestAsset\TestSampleClass2',
            '_prop2'
        );

        $annotationManager = new \Laminas\Code\Annotation\AnnotationManager();

        $fileScanner = $this->getMockBuilder('Laminas\Code\Scanner\CachingFileScanner')
                            ->disableOriginalConstructor()
                            ->getMock();

        $reflectionProperty->setFileScanner($fileScanner);

        $fileScanner->expects($this->any())
                    ->method('getClassNameInformation')
                    ->will($this->returnValue(false));

        $this->assertFalse($reflectionProperty->getAnnotations($annotationManager));
    }
}
