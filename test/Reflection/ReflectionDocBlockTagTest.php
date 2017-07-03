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
use Zend\Code\Reflection;

/**
 * @group      Zend_Reflection
 * @group      Zend_Reflection_DocBlock
 * @group      Zend_Reflection_DocBlock_Tag
 */
class ReflectionDocBlockTagTest extends TestCase
{
    public function testTagDescriptionIsReturned()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass5::class);

        $authorTag = $classReflection->getDocBlock()->getTag('author');
        self::assertEquals('Ralph Schindler', $authorTag->getAuthorName());
        self::assertEquals('ralph.schindler@zend.com', $authorTag->getAuthorEmail());
    }

    public function testTagShouldAllowJustTagNameInDocBlockTagLine()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass6::class);

        $tag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('emptyTag');
        self::assertEquals($tag->getName(), 'emptyTag', 'Factory First Match Failed');
    }

    public function testTagShouldAllowMultipleWhitespacesBeforeDescription()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass6::class);

        $tag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('descriptionTag');
        self::assertNotEquals('          A tag with just a description', $tag->getContent(), 'Final Match Failed');
        self::assertEquals('A tag with just a description', $tag->getContent(), 'Final Match Failed');
    }

    public function testToString()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass6::class);

        $tag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('descriptionTag');

        $expectedString = 'DocBlock Tag [ * @descriptionTag ]' . "\n";

        self::assertEquals($expectedString, (string) $tag);
    }


    public function testTypeParam()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass5::class);

        $paramTag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('param');

        self::assertEquals($paramTag->getType(), 'int');
    }

    public function testVariableName()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass5::class);

        $paramTag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('param');
        self::assertEquals($paramTag->getVariableName(), '$one');
    }

    public function testAllowsMultipleSpacesInDocBlockTagLine()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass6::class);

        $paramTag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('param');


        self::assertEquals($paramTag->getType(), 'int', 'Second Match Failed');
        self::assertEquals($paramTag->getVariableName(), '$var', 'Third Match Failed');
        self::assertEquals($paramTag->getDescription(), 'Description of $var', 'Final Match Failed');
    }


    /**
     * @group ZF-8307
     */
    public function testNamespaceInParam()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass7::class);
        $paramTag        = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('param');


        self::assertEquals('Zend\Foo\Bar', $paramTag->getType());
        self::assertEquals('$var', $paramTag->getVariableName());
        self::assertEquals('desc', $paramTag->getDescription());
    }

    public function testType()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass5::class);

        $paramTag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('return');
        self::assertEquals($paramTag->getType(), 'mixed');
    }

    public function testAllowsMultipleSpacesInDocBlockTagLine2()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass6::class);

        $paramTag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('return');

        self::assertEquals($paramTag->getType(), 'string', 'Second Match Failed');
        self::assertEquals($paramTag->getDescription(), 'Description of return value', 'Final Match Failed');
    }


    /**
     * @group ZF-8307
     */
    public function testReturnClassWithNamespace()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass7::class);

        $paramTag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('return');

        self::assertEquals('Zend\Code\Reflection\DocBlock', $paramTag->getType());
    }
}
