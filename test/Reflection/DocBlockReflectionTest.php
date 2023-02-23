<?php

namespace LaminasTest\Code\Reflection;

use Laminas\Code\Reflection\ClassReflection;
use Laminas\Code\Reflection\DocBlock\Tag\MethodTag;
use Laminas\Code\Reflection\DocBlock\Tag\ParamTag;
use Laminas\Code\Reflection\DocBlock\Tag\PropertyTag;
use Laminas\Code\Reflection\DocBlock\Tag\ReturnTag;
use Laminas\Code\Reflection\DocBlock\Tag\TagInterface;
use Laminas\Code\Reflection\DocBlock\Tag\ThrowsTag;
use Laminas\Code\Reflection\DocBlockReflection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Laminas_Reflection')]
#[Group('Laminas_Reflection_DocBlock')]
class DocBlockReflectionTest extends TestCase
{
    public function testDocBlockShortDescription()
    {
        $classReflection = new ClassReflection(TestAsset\TestSampleClass5::class);
        self::assertEquals(
            'TestSampleClass5 DocBlock Short Desc',
            $classReflection->getDocBlock()->getShortDescription()
        );
    }

    public function testDocBlockLongDescription()
    {
        $classReflection = new ClassReflection(TestAsset\TestSampleClass5::class);
        $expectedOutput  = 'This is a long description for the docblock of this class, it should be longer '
            . 'than 3 lines. It indeed is longer than 3 lines now.';

        self::assertEquals($expectedOutput, $classReflection->getDocBlock()->getLongDescription());
    }

    public function testDocBlockTags()
    {
        $classReflection = new ClassReflection(TestAsset\TestSampleClass5::class);

        self::assertCount(3, $classReflection->getDocBlock()->getTags());
        self::assertCount(1, $classReflection->getDocBlock()->getTags('author'));
        self::assertCount(1, $classReflection->getDocBlock()->getTags('property'));
        self::assertCount(1, $classReflection->getDocBlock()->getTags('method'));

        $methodTag = $classReflection->getDocBlock()->getTag('method');
        self::assertInstanceOf(MethodTag::class, $methodTag);

        $propertyTag = $classReflection->getDocBlock()->getTag('property');
        self::assertInstanceOf(PropertyTag::class, $propertyTag);

        self::assertFalse($classReflection->getDocBlock()->getTag('version'));

        self::assertTrue($classReflection->getMethod('doSomething')->getDocBlock()->hasTag('return'));

        $returnTag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('return');
        self::assertInstanceOf(TagInterface::class, $returnTag);
        self::assertEquals('mixed', $returnTag->getType());
    }

    public function testShortDocBlocks()
    {
        $classReflection = new ClassReflection(TestAsset\TestSampleClass13::class);
        self::assertCount(0, $classReflection->getDocBlock()->getTags());

        self::assertSame(
            'Short Method Description',
            $classReflection->getMethod('doSomething')->getDocBlock()->getShortDescription()
        );
        self::assertSame('Short Class Description', $classReflection->getDocBlock()->getShortDescription());

        $returnTag = $classReflection->getMethod('returnSomething')->getDocBlock()->getTag('return');
        self::assertInstanceOf(TagInterface::class, $returnTag);
        self::assertEquals('Something', $returnTag->getType());
        self::assertEquals('This describes something', $returnTag->getDescription());
    }

    public function testTabbedDocBlockTags()
    {
        $classReflection = new ClassReflection(TestAsset\TestSampleClass10::class);

        self::assertCount(3, $classReflection->getDocBlock()->getTags());
        self::assertCount(1, $classReflection->getDocBlock()->getTags('author'));
        self::assertCount(1, $classReflection->getDocBlock()->getTags('property'));
        self::assertCount(1, $classReflection->getDocBlock()->getTags('method'));

        $methodTag = $classReflection->getDocBlock()->getTag('method');
        self::assertInstanceOf(MethodTag::class, $methodTag);

        $propertyTag = $classReflection->getDocBlock()->getTag('property');
        self::assertInstanceOf(PropertyTag::class, $propertyTag);

        self::assertFalse($classReflection->getDocBlock()->getTag('version'));

        self::assertTrue($classReflection->getMethod('doSomething')->getDocBlock()->hasTag('return'));

        $returnTag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('return');
        self::assertInstanceOf(TagInterface::class, $returnTag);
        self::assertEquals('mixed', $returnTag->getType());
    }

    public function testDocBlockLines()
    {
        $classReflection = new ClassReflection(TestAsset\TestSampleClass5::class);

        $classDocBlock = $classReflection->getDocBlock();

        self::assertEquals(5, $classDocBlock->getStartLine());
        self::assertEquals(17, $classDocBlock->getEndLine());
    }

    public function testDocBlockContents()
    {
        $classReflection = new ClassReflection(TestAsset\TestSampleClass5::class);

        $classDocBlock = $classReflection->getDocBlock();

        $expectedContents = <<<EOS
TestSampleClass5 DocBlock Short Desc

This is a long description for
the docblock of this class, it
should be longer than 3 lines.
It indeed is longer than 3 lines
now.

@author Ralph Schindler <ralph.schindler@zend.com>
@method test()
@property \$test

EOS;

        self::assertEquals($expectedContents, $classDocBlock->getContents());
    }

    public function testToString()
    {
        $classReflection = new ClassReflection(TestAsset\TestSampleClass5::class);

        $classDocBlock = $classReflection->getDocBlock();

        $expectedString = 'DocBlock [ /* DocBlock */ ] {' . "\n"
                        . "\n"
                        . '  - Tags [3] {' . "\n"
                        . '    DocBlock Tag [ * @author ]' . "\n"
                        . '    DocBlock Tag [ * @method ]' . "\n"
                        . '    DocBlock Tag [ * @property ]' . "\n"
                        . '  }' . "\n"
                        . '}' . "\n";

        self::assertEquals($expectedString, (string) $classDocBlock);
    }

    public function testFunctionDocBlockTags()
    {
        $docblock = '
    /**
     * Method ShortDescription
     *
     * @param int $one Description for one
     * @param int[] Description for two
     * @param string|null $three Description for three
     *                      which spans multiple lines
     * @return int[]|null Description
     * @throws Exception
     */
';

        $docblockReflection = new DocBlockReflection($docblock);

        $paramTags = $docblockReflection->getTags('param');

        self::assertCount(5, $docblockReflection->getTags());
        self::assertCount(3, $paramTags);
        self::assertCount(1, $docblockReflection->getTags('return'));
        self::assertCount(1, $docblockReflection->getTags('throws'));

        $returnTag = $docblockReflection->getTag('return');
        self::assertInstanceOf(ReturnTag::class, $returnTag);
        self::assertEquals('int[]', $returnTag->getType());
        self::assertEquals(['int[]', 'null'], $returnTag->getTypes());
        self::assertEquals('Description', $returnTag->getDescription());

        $throwsTag = $docblockReflection->getTag('throws');
        self::assertInstanceOf(ThrowsTag::class, $throwsTag);
        self::assertEquals('Exception', $throwsTag->getType());

        $paramTag = $paramTags[0];
        self::assertInstanceOf(ParamTag::class, $paramTag);
        self::assertEquals('int', $paramTag->getType());

        $paramTag = $paramTags[1];
        self::assertInstanceOf(ParamTag::class, $paramTag);
        self::assertEquals('int[]', $paramTag->getType());

        $paramTag = $paramTags[2];
        self::assertInstanceOf(ParamTag::class, $paramTag);
        self::assertEquals('string', $paramTag->getType());
        self::assertEquals(['string', 'null'], $paramTag->getTypes());
    }
}
