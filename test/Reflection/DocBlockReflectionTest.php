<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection;

use Laminas\Code\Reflection\ClassReflection;

/**
 * @category   Laminas
 * @package    Laminas_Reflection
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2012 Laminas (https://www.zend.com)
 * @license    https://getlaminas.org/license/new-bsd     New BSD License
 * @group      Laminas_Reflection
 * @group      Laminas_Reflection_DocBlock
 */
class DocBlockReflectionTest extends \PHPUnit_Framework_TestCase
{
    public function testDocBlockShortDescription()
    {
        $classReflection = new ClassReflection('LaminasTest\Code\Reflection\TestAsset\TestSampleClass5');
        $this->assertEquals('TestSampleClass5 DocBlock Short Desc', $classReflection->getDocBlock()->getShortDescription());
    }

    public function testDocBlockLongDescription()
    {
        $classReflection = new ClassReflection('LaminasTest\Code\Reflection\TestAsset\TestSampleClass5');
        $expectedOutput = 'This is a long description for the docblock of this class, it should be longer than 3 lines. It indeed is longer than 3 lines now.';


        $this->assertEquals($expectedOutput, $classReflection->getDocBlock()->getLongDescription());

    }

    public function testDocBlockTags()
    {
        $classReflection = new ClassReflection('LaminasTest\Code\Reflection\TestAsset\TestSampleClass5');

        $this->assertEquals(3, count($classReflection->getDocBlock()->getTags()));
        $this->assertEquals(1, count($classReflection->getDocBlock()->getTags('author')));
        $this->assertEquals(1, count($classReflection->getDocBlock()->getTags('property')));
        $this->assertEquals(1, count($classReflection->getDocBlock()->getTags('method')));

        $methodTag = $classReflection->getDocBlock()->getTag('method');
        $this->assertInstanceOf('Laminas\Code\Reflection\DocBlock\Tag\MethodTag', $methodTag);

        $propertyTag = $classReflection->getDocBlock()->getTag('property');
        $this->assertInstanceOf('Laminas\Code\Reflection\DocBlock\Tag\PropertyTag', $propertyTag);

        $this->assertFalse($classReflection->getDocBlock()->getTag('version'));

        $this->assertTrue($classReflection->getMethod('doSomething')->getDocBlock()->hasTag('return'));

        $returnTag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('return');
        $this->assertInstanceOf('Laminas\Code\Reflection\DocBlock\Tag\TagInterface', $returnTag);
        $this->assertEquals('mixed', $returnTag->getType());


    }

    public function testDocBlockLines()
    {
        //$this->markTestIncomplete('Line numbers incomplete');

        $classReflection = new ClassReflection('LaminasTest\Code\Reflection\TestAsset\TestSampleClass5');

        $classDocBlock = $classReflection->getDocBlock();

        $this->assertEquals(5, $classDocBlock->getStartLine());
        $this->assertEquals(17, $classDocBlock->getEndLine());

    }

    public function testDocBlockContents()
    {
        $classReflection = new ClassReflection('LaminasTest\Code\Reflection\TestAsset\TestSampleClass5');

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

        $this->assertEquals($expectedContents, $classDocBlock->getContents());

    }

    public function testToString()
    {
        $classReflection = new ClassReflection('LaminasTest\Code\Reflection\TestAsset\TestSampleClass5');

        $classDocBlock = $classReflection->getDocBlock();

        $expectedString = 'DocBlock [ /* DocBlock */ ] {' . PHP_EOL
                        . PHP_EOL
                        . '  - Tags [3] {' . PHP_EOL
                        . '    DocBlock Tag [ * @author ]' . PHP_EOL
                        . '    DocBlock Tag [ * @method ]' . PHP_EOL
                        . '    DocBlock Tag [ * @property ]' . PHP_EOL
                        . '  }' . PHP_EOL
                        . '}' . PHP_EOL;

        $this->assertEquals($expectedString, (string)$classDocBlock);
    }
}
