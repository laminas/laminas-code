<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection;

use Laminas\Code\Annotation\AnnotationManager;
use Laminas\Code\Annotation\Parser\GenericAnnotationParser;

/**
 * @category   Laminas
 * @package    Laminas_Reflection
 * @subpackage UnitTests
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
}
