<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\FileGenerator;
use Laminas\Code\Reflection\FileReflection;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 * @group Laminas_Code_Generator_Php_File
 */
class FileGeneratorTest extends \PHPUnit_Framework_TestCase
{

    public function testConstruction()
    {
        $file = new FileGenerator();
        $this->assertEquals('Laminas\Code\Generator\FileGenerator', get_class($file));
    }

    public function testSourceContentGetterAndSetter()
    {
        $file = new FileGenerator();
        $file->setSourceContent('Foo');
        $this->assertEquals('Foo', $file->getSourceContent());
    }

    public function testIndentationGetterAndSetter()
    {
        $file = new FileGenerator();
        $file->setIndentation('        ');
        $this->assertEquals('        ', $file->getIndentation());
    }

    public function testToString()
    {
        $codeGenFile = FileGenerator::fromArray(array(
            'requiredFiles' => array('SampleClass.php'),
            'class' => array(
                'flags' => ClassGenerator::FLAG_ABSTRACT,
                'name' => 'SampleClass',
                'extendedClass' => 'ExtendedClassName',
                'implementedInterfaces' => array('Iterator', 'Traversable')
            )
        ));


        $expectedOutput = <<<EOS
<?php

require_once 'SampleClass.php';

abstract class SampleClass extends ExtendedClassName implements Iterator, Traversable
{


}


EOS;

        $output = $codeGenFile->generate();
        $this->assertEquals($expectedOutput, $output, $output);
    }

    public function testFromReflection()
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'UnitFile');

        $codeGenFile = FileGenerator::fromArray(array(
            'class' => array(
                'name' => 'SampleClass'
            )
        ));

        file_put_contents($tempFile, $codeGenFile->generate());

        require_once $tempFile;

        $fileGenerator = FileGenerator::fromReflection(new FileReflection($tempFile));

        unlink($tempFile);

        $this->assertEquals('Laminas\Code\Generator\FileGenerator', get_class($fileGenerator));
        $this->assertEquals(1, count($fileGenerator->getClasses()));

    }

    public function testFromFileReflection()
    {
        $this->markTestIncomplete('Some scanning capabilities are incomplete, including file DocBlock comment retrieval and method scanning');

        $file = __DIR__ . '/TestAsset/TestSampleSingleClass.php';
        require_once $file;

        $codeGenFileFromDisk = FileGenerator::fromReflection($fileRefl = new FileReflection($file));

        $codeGenFileFromDisk->getClass()->addMethod('foobar');

        $expectedOutput = <<<EOS
<?php
/**
 * File header here
 *
 * @author Ralph Schindler <ralph.schindler@zend.com>
 */



/* Laminas_Code_Generator_FileGenerator-ClassMarker: {LaminasTest\Code\Generator\TestAsset\TestSampleSingleClass} */


namespace LaminasTest\Code\Generator\TestAsset;

/**
 * class docblock
 *
 */
class TestSampleSingleClass
{

    /**
     * Enter description here...
     *
     * @return bool
     *
     */
    public function someMethod()
    {
        /* test test */
    }

    public function foobar()
    {
    }


}


EOS;
        $this->assertEquals($expectedOutput, $codeGenFileFromDisk->generate());
    }

    public function testFileLineEndingsAreAlwaysLineFeed()
    {
        $codeGenFile = FileGenerator::fromArray(array(
            'requiredFiles' => array('SampleClass.php'),
            'class' => array(
                'abstract' => true,
                'name' => 'SampleClass',
                'extendedClass' => 'ExtendedClassName',
                'implementedInterfaces' => array('Iterator', 'Traversable'),
            ),
        ));

        // explode by newline, this would leave CF in place if it were generated
        $lines = explode("\n", $codeGenFile->generate());

        $targetLength = strlen('require_once \'SampleClass.php\';');
        $this->assertEquals($targetLength, strlen($lines[2]));
        $this->assertEquals(';', $lines[2]{$targetLength-1});
    }

    /**
     * @group Laminas-11218
     */
    public function testGeneratesUseStatements()
    {
        $file = new FileGenerator();
        $file->setUse('My\Baz')
             ->setUses(array(
                 array('use' => 'Your\Bar', 'as' => 'bar'),
             ));
        $generated = $file->generate();
        $this->assertContains('use My\\Baz;', $generated);
        $this->assertContains('use Your\\Bar as bar;', $generated);
    }

    public function testGeneratesNamespaceStatements()
    {
        $file = new FileGenerator();
        $file->setNamespace('Foo\Bar');
        $generated = $file->generate();
        $this->assertContains('namespace Foo\\Bar', $generated, $generated);
    }

    public function testSetUseDoesntGenerateMultipleIdenticalUseStatements()
    {
        $file = new FileGenerator();
        $file->setUse('My\Baz')
             ->setUse('My\Baz');
        $generated = $file->generate();
        $this->assertSame(strpos($generated, 'use My\\Baz'), strrpos($generated, 'use My\\Baz'));
    }

    public function testSetUsesDoesntGenerateMultipleIdenticalUseStatements()
    {
        $file = new FileGenerator();
        $file->setUses(array(
                 array('use' => 'Your\Bar', 'as' => 'bar'),
                 array('use' => 'Your\Bar', 'as' => 'bar'),
        ));
        $generated = $file->generate();
        $this->assertSame(strpos($generated, 'use Your\\Bar as bar;'), strrpos($generated, 'use Your\\Bar as bar;'));
    }

    public function testSetUseAllowsMultipleAliasedUseStatements()
    {
        $file = new FileGenerator();
        $file->setUses(array(
                 array('use' => 'Your\Bar', 'as' => 'bar'),
                 array('use' => 'Your\Bar', 'as' => 'bar2'),
        ));
        $generated = $file->generate();
        $this->assertContains('use Your\\Bar as bar;', $generated);
        $this->assertContains('use Your\\Bar as bar2;', $generated);
    }

    public function testSetUsesWithArrays()
    {
        $file = new FileGenerator();
        $file->setUses(array(
                 array('use' => 'Your\\Bar', 'as' => 'bar'),
                 array('use' => 'My\\Baz', 'as' => 'FooBaz')
             ));
        $generated = $file->generate();
        $this->assertContains('use My\\Baz as FooBaz;', $generated);
        $this->assertContains('use Your\\Bar as bar;', $generated);
    }

    public function testSetUsesWithString()
    {
        $file = new FileGenerator();
        $file->setUses(array(
            'Your\\Bar',
            'My\\Baz',
            array('use' => 'Another\\Baz', 'as' => 'Baz2')
        ));
        $generated = $file->generate();
        $this->assertContains('use My\\Baz;', $generated);
        $this->assertContains('use Your\\Bar;', $generated);
        $this->assertContains('use Another\\Baz as Baz2;', $generated);
    }

    public function testSetUsesWithGetUses()
    {
        $file = new FileGenerator();
        $uses = array(
            'Your\\Bar',
            'My\\Baz',
            array('use' => 'Another\\Baz', 'as' => 'Baz2')
        );
        $file->setUses($uses);
        $file->setUses($file->getUses());
        $generated = $file->generate();
        $this->assertContains('use My\\Baz;', $generated);
        $this->assertContains('use Your\\Bar;', $generated);
        $this->assertContains('use Another\\Baz as Baz2;', $generated);
    }

    public function testCreateFromArrayWithClassInstance()
    {
        $fileGenerator = FileGenerator::fromArray(array(
            'filename'  => 'foo.php',
            'class'     => new ClassGenerator('bar'),
        ));
        $class = $fileGenerator->getClass('bar');
        $this->assertInstanceOf('Laminas\Code\Generator\ClassGenerator', $class);
    }

    public function testCreateFromArrayWithClassFromArray()
    {
        $fileGenerator = FileGenerator::fromArray(array(
            'filename'  => 'foo.php',
            'class'     => array(
                'name' => 'bar',
            ),
        ));
        $class = $fileGenerator->getClass('bar');
        $this->assertInstanceOf('Laminas\Code\Generator\ClassGenerator', $class);
    }

    public function testGeneratingFromAReflectedFileName()
    {
        $generator = FileGenerator::fromReflectedFileName(__DIR__ . '/TestAsset/OneInterface.php');
        $this->assertInstanceOf('Laminas\Code\Generator\FileGenerator', $generator);
    }

    public function testGeneratedClassesHaveUses()
    {
        $generator = FileGenerator::fromReflectedFileName(__DIR__ . '/TestAsset/ClassWithUses.php');
        $class = $generator->getClass();

        $expectedUses = array('LaminasTest\Code\Generator\TestAsset\ClassWithNamespace');

        $this->assertEquals($expectedUses, $class->getUses());
    }
}
