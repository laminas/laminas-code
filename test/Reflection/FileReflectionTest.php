<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection;

use Exception;
use Laminas\Code\Reflection\DocBlockReflection;
use Laminas\Code\Reflection\Exception\InvalidArgumentException;
use Laminas\Code\Reflection\Exception\RuntimeException;
use Laminas\Code\Reflection\FileReflection;
use Laminas\Code\Reflection\FunctionReflection;
use PHPUnit\Framework\TestCase;

use function current;
use function get_class;
use function get_include_path;
use function get_included_files;
use function realpath;
use function set_include_path;

/**
 * @group      Laminas_Reflection
 * @group      Laminas_Reflection_File
 */
class FileReflectionTest extends TestCase
{
    public function testFileConstructorThrowsExceptionOnNonExistentFile()
    {
        $nonExistentFile = 'Non/Existent/File.php';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('found');
        new FileReflection($nonExistentFile);
    }

    public function testFileConstructorFromAReflectedFilenameInIncludePathWithoutIncludeFlagEnabled()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('must be required');
        $oldIncludePath = set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/TestAsset/');

        try {
            new FileReflection('an_empty_file.php', false);
            set_include_path($oldIncludePath);
            $this->fail('Should throw exception');
        } catch (Exception $e) {
            set_include_path($oldIncludePath);
            throw $e;
        }
    }

    public function testFileConstructorFromAReflectedFilenameIncluded()
    {
        include_once __DIR__ . '/TestAsset/an_empty_file.php';

        $oldIncludePath = set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/TestAsset/');

        try {
            $file = new FileReflection('an_empty_file.php', false);

            self::assertSame('an_empty_file.php', $file->getFileName());
        } finally {
            set_include_path($oldIncludePath);
        }
    }

    public function testFileConstructorFromAReflectedFilenameInIncludePath()
    {
        self::assertNotContains(realpath(__DIR__ . '/TestAsset/a_second_empty_file.php'), get_included_files());
        $oldIncludePath = set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/TestAsset/');

        try {
            new FileReflection('a_second_empty_file.php', true);
            set_include_path($oldIncludePath);
        } catch (Exception $e) {
            set_include_path($oldIncludePath);
            throw $e;
        }
    }

    public function testFileGetClassReturnsClassReflectionObject()
    {
        $fileToReflect = __DIR__ . '/TestAsset/TestSampleClass.php';
        include_once $fileToReflect;
        $reflectionFile = new FileReflection($fileToReflect);
        self::assertEquals(get_class($reflectionFile), FileReflection::class);
        self::assertCount(1, $reflectionFile->getClasses());
    }

    public function testFileGetClassReturnsFirstClassWithNoOptions()
    {
        $fileToReflect = __DIR__ . '/TestAsset/TestSampleClass.php';
        include_once $fileToReflect;
        $reflectionFile = new FileReflection($fileToReflect);
        self::assertEquals(TestAsset\TestSampleClass::class, $reflectionFile->getClass()->getName());
    }

    public function testFileGetClassThrowsExceptionOnNonExistentClassName()
    {
        $fileToReflect = __DIR__ . '/TestAsset/TestSampleClass.php';
        include_once $fileToReflect;
        $reflectionFile = new FileReflection($fileToReflect);
        $nonExistentClass = 'Some_Non_Existent_Class';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class by name Some_Non_Existent_Class not found');
        $reflectionFile->getClass($nonExistentClass);
    }

    public function testFileReflectorRequiredFunctionsDoNothing()
    {
        self::assertNull(FileReflection::export());

        $reflectionFile = new FileReflection(__FILE__);
        self::assertEquals('', $reflectionFile->__toString());
    }

    public function testFileGetFilenameReturnsCorrectFilename()
    {
        $reflectionFile = new FileReflection(__FILE__);

        self::assertEquals('FileReflectionTest.php', $reflectionFile->getFileName());
    }

    public function testFileGetLineNumbersWorks()
    {
        $this->markTestIncomplete('Line numbering not implemented yet');

        $fileToReflect = __DIR__ . '/TestAsset/TestSampleClass.php';
        include_once $fileToReflect;
        $reflectionFile = new FileReflection($fileToReflect);
        self::assertEquals(9, $reflectionFile->getStartLine());
        self::assertEquals(24, $reflectionFile->getEndLine());
    }

    public function testFileGetDocBlockReturnsFileDocBlock()
    {
        $fileToReflect = __DIR__ . '/TestAsset/TestSampleClass7.php';
        include_once $fileToReflect;
        $reflectionFile = new FileReflection($fileToReflect);

        $reflectionDocBlock = $reflectionFile->getDocBlock();
        self::assertInstanceOf(DocBlockReflection::class, $reflectionDocBlock);

        $authorTag = $reflectionDocBlock->getTag('author');
        self::assertEquals('Jeremiah Small', $authorTag->getAuthorName());
        self::assertEquals('jsmall@soliantconsulting.com', $authorTag->getAuthorEmail());
    }

    public function testFileGetFunctionsReturnsFunctions()
    {
        $this->markTestIncomplete('Function scanning not implemented yet');

        $fileToRequire = __DIR__ . '/TestAsset/FileOfFunctions.php';
        include_once $fileToRequire;
        $reflectionFile = new FileReflection($fileToRequire);
        $funcs = $reflectionFile->getFunctions();
        self::assertInstanceOf(FunctionReflection::class, current($funcs));
    }

    public function testFileCanReflectFileWithInterface()
    {
        $fileToReflect = __DIR__ . '/TestAsset/TestSampleInterface.php';
        include_once $fileToReflect;
        $reflectionFile = new FileReflection($fileToReflect);
        $class = $reflectionFile->getClass();
        self::assertEquals(TestAsset\TestSampleInterface::class, $class->getName());
        self::assertTrue($class->isInterface());
    }

    public function testFileCanReflectFileWithUses()
    {
        $fileToReflect = __DIR__ . '/TestAsset/TestSampleClass8.php';
        include_once $fileToReflect;
        $reflectionFile = new FileReflection($fileToReflect);
        $expected = [
            ['use' => 'Laminas\Config', 'as' => 'LaminasConfig'],
            ['use' => 'FooBar\Foo\Bar', 'as' => null],
            ['use' => 'One\Two\Three\Four\Five', 'as' => 'ottff'],
        ];
        self::assertSame($expected, $reflectionFile->getUses());
    }

    /**
     * @group 70
     * @group 43
     */
    public function testFileReflectionShouldNotRaiseNoticesWhenReflectingClosures()
    {
        require_once __DIR__ . '/TestAsset/issue-70.php';
        $r = new FileReflection(__DIR__ . '/TestAsset/issue-70.php');
        self::assertStringContainsString('spl_autoload_register', $r->getContents());
        self::assertStringContainsString('function ()', $r->getContents());
    }
}
