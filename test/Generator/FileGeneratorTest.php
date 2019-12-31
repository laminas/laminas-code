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
use PHPUnit\Framework\TestCase;

use function explode;
use function file_get_contents;
use function file_put_contents;
use function get_class;
use function strlen;
use function strpos;
use function strrpos;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 * @group Laminas_Code_Generator_Php_File
 */
class FileGeneratorTest extends TestCase
{
    public function testConstruction()
    {
        $file = new FileGenerator();
        self::assertEquals(FileGenerator::class, get_class($file));
    }

    public function testSourceContentGetterAndSetter()
    {
        $file = new FileGenerator();
        $file->setSourceContent('Foo');
        self::assertEquals('Foo', $file->getSourceContent());
    }

    public function testIndentationGetterAndSetter()
    {
        $file = new FileGenerator();
        $file->setIndentation('        ');
        self::assertEquals('        ', $file->getIndentation());
    }

    public function testToString()
    {
        $codeGenFile = FileGenerator::fromArray([
            'requiredFiles' => ['SampleClass.php'],
            'class' => [
                'flags' => ClassGenerator::FLAG_ABSTRACT,
                'name' => 'SampleClass',
                'extendedClass' => 'ExtendedClassName',
                'implementedInterfaces' => ['Iterator', 'Traversable'],
            ],
        ]);

        $expectedOutput = <<<EOS
<?php

require_once 'SampleClass.php';

abstract class SampleClass extends ExtendedClassName implements Iterator, Traversable
{


}


EOS;

        $output = $codeGenFile->generate();
        self::assertEquals($expectedOutput, $output, $output);
    }

    public function testFromReflection()
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'UnitFile');

        $codeGenFile = FileGenerator::fromArray([
            'class' => [
                'name' => 'SampleClass',
            ],
        ]);

        file_put_contents($tempFile, $codeGenFile->generate());

        require_once $tempFile;

        $fileGenerator = FileGenerator::fromReflection(new FileReflection($tempFile));

        unlink($tempFile);

        self::assertEquals(FileGenerator::class, get_class($fileGenerator));
        self::assertCount(1, $fileGenerator->getClasses());
    }

    public function testFromFileReflection()
    {
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


namespace LaminasTest\Code\Generator\TestAsset;

/**
 * class docblock
 */
class TestSampleSingleClass
{

    /**
     * Enter description here...
     *
     * @return bool
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

        self::assertEquals($expectedOutput, $codeGenFileFromDisk->generate());
    }

    /**
     * @group test
     */
    public function testFileLineEndingsAreAlwaysLineFeed()
    {
        $codeGenFile = FileGenerator::fromArray([
            'requiredFiles' => ['SampleClass.php'],
            'class' => [
                'abstract' => true,
                'name' => 'SampleClass',
                'extendedClass' => 'ExtendedClassName',
                'implementedInterfaces' => ['Iterator', 'Traversable'],
            ],
        ]);

        // explode by newline, this would leave CF in place if it were generated
        $lines = explode("\n", $codeGenFile->generate());

        $targetLength = strlen('require_once \'SampleClass.php\';');
        self::assertEquals($targetLength, strlen($lines[2]));
        self::assertEquals(';', $lines[2]{$targetLength - 1});
    }

    /**
     * @group Laminas-11218
     */
    public function testGeneratesUseStatements()
    {
        $file = new FileGenerator();
        $file->setUse('My\Baz')
             ->setUses([
                 ['use' => 'Your\Bar', 'as' => 'bar'],
             ]);
        $generated = $file->generate();
        self::assertContains('use My\\Baz;', $generated);
        self::assertContains('use Your\\Bar as bar;', $generated);
    }

    public function testGeneratesNamespaceStatements()
    {
        $file = new FileGenerator();
        $file->setNamespace('Foo\Bar');
        $generated = $file->generate();
        self::assertContains('namespace Foo\\Bar', $generated, $generated);
    }

    public function testSetUseDoesntGenerateMultipleIdenticalUseStatements()
    {
        $file = new FileGenerator();
        $file->setUse('My\Baz')
             ->setUse('My\Baz');
        $generated = $file->generate();
        self::assertSame(strpos($generated, 'use My\\Baz'), strrpos($generated, 'use My\\Baz'));
    }

    public function testSetUsesDoesntGenerateMultipleIdenticalUseStatements()
    {
        $file = new FileGenerator();
        $file->setUses([
            ['use' => 'Your\Bar', 'as' => 'bar'],
            ['use' => 'Your\Bar', 'as' => 'bar'],
        ]);
        $generated = $file->generate();
        self::assertSame(strpos($generated, 'use Your\\Bar as bar;'), strrpos($generated, 'use Your\\Bar as bar;'));
    }

    public function testSetUseAllowsMultipleAliasedUseStatements()
    {
        $file = new FileGenerator();
        $file->setUses([
            ['use' => 'Your\Bar', 'as' => 'bar'],
            ['use' => 'Your\Bar', 'as' => 'bar2'],
        ]);
        $generated = $file->generate();
        self::assertContains('use Your\\Bar as bar;', $generated);
        self::assertContains('use Your\\Bar as bar2;', $generated);
    }

    public function testSetUsesWithArrays()
    {
        $file = new FileGenerator();
        $file->setUses([
            ['use' => 'Your\\Bar', 'as' => 'bar'],
            ['use' => 'My\\Baz', 'as' => 'FooBaz'],
        ]);
        $generated = $file->generate();
        self::assertContains('use My\\Baz as FooBaz;', $generated);
        self::assertContains('use Your\\Bar as bar;', $generated);
    }

    public function testSetUsesWithString()
    {
        $file = new FileGenerator();
        $file->setUses([
            'Your\\Bar',
            'My\\Baz',
            ['use' => 'Another\\Baz', 'as' => 'Baz2'],
        ]);
        $generated = $file->generate();
        self::assertContains('use My\\Baz;', $generated);
        self::assertContains('use Your\\Bar;', $generated);
        self::assertContains('use Another\\Baz as Baz2;', $generated);
    }

    public function testSetUsesWithGetUses()
    {
        $file = new FileGenerator();
        $uses = [
            'Your\\Bar',
            'My\\Baz',
            ['use' => 'Another\\Baz', 'as' => 'Baz2'],
        ];
        $file->setUses($uses);
        $file->setUses($file->getUses());
        $generated = $file->generate();
        self::assertContains('use My\\Baz;', $generated);
        self::assertContains('use Your\\Bar;', $generated);
        self::assertContains('use Another\\Baz as Baz2;', $generated);
    }

    public function testCreateFromArrayWithClassInstance()
    {
        $fileGenerator = FileGenerator::fromArray([
            'filename'  => 'foo.php',
            'class'     => new ClassGenerator('bar'),
        ]);
        $class = $fileGenerator->getClass('bar');
        self::assertInstanceOf(ClassGenerator::class, $class);
    }

    public function testCreateFromArrayWithClassFromArray()
    {
        $fileGenerator = FileGenerator::fromArray([
            'filename'  => 'foo.php',
            'class'     => [
                'name' => 'bar',
            ],
        ]);
        $class = $fileGenerator->getClass('bar');
        self::assertInstanceOf(ClassGenerator::class, $class);
    }

    public function testGeneratingFromAReflectedFileName()
    {
        $generator = FileGenerator::fromReflectedFileName(__DIR__ . '/TestAsset/OneInterface.php');
        self::assertInstanceOf(FileGenerator::class, $generator);
    }

    public function testGeneratedClassesHaveUses()
    {
        $generator = FileGenerator::fromReflectedFileName(__DIR__ . '/TestAsset/ClassWithUses.php');
        $class = $generator->getClass();

        $expectedUses = [TestAsset\ClassWithNamespace::class];

        self::assertEquals($expectedUses, $class->getUses());
    }

    /**
     * @group 4747
     */
    public function testIssue4747FileGenerationWithAddedMethodIsCorrectlyFormatted()
    {
        $g = new FileGenerator();
        $g = $g->fromReflectedFileName(__DIR__ . '/TestAsset/ClassWithUses.php');
        $g->setFilename(sys_get_temp_dir() . '/result_class.php');
        $g->getClass()->addMethod('added');
        $g->write();

        $expected = <<<'CODE'
<?php
/**
 * @see       https://github.com/laminas/laminas-code for the canonical source
 * repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New
 * BSD License
 */


namespace LaminasTest\Code\Generator\TestAsset;


use LaminasTest\Code\Generator\TestAsset\ClassWithNamespace;

class ClassWithUses
{

    public function added()
    {
    }


}


CODE;
        $actual = file_get_contents(sys_get_temp_dir() . '/result_class.php');
        self::assertEquals($expected, $actual);
    }

    /**
     * @group 4747
     */
    public function testCanAppendToBodyOfReflectedFile()
    {
        $g = new FileGenerator();
        $g = $g->fromReflectedFileName(__DIR__ . '/TestAsset/ClassWithUses.php');
        $g->setFilename(sys_get_temp_dir() . '/result_class.php');
        $g->getClass()->addMethod('added');
        $g->setBody('$foo->bar();');
        $g->write();

        $expected = <<<'CODE'
<?php
/**
 * @see       https://github.com/laminas/laminas-code for the canonical source
 * repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New
 * BSD License
 */


namespace LaminasTest\Code\Generator\TestAsset;


use LaminasTest\Code\Generator\TestAsset\ClassWithNamespace;

class ClassWithUses
{

    public function added()
    {
    }


}


$foo->bar();
CODE;
        $actual = file_get_contents(sys_get_temp_dir() . '/result_class.php');
        self::assertEquals($expected, $actual);
    }
}
