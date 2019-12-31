<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection;

use Exception;
use Laminas\Code\Reflection\FileReflection;

/**
 * @group      Laminas_Reflection
 * @group      Laminas_Reflection_File
 */
class FileReflectionTest extends \PHPUnit_Framework_TestCase
{
    public function testFileConstructorThrowsExceptionOnNonExistentFile()
    {
        $nonExistentFile = 'Non/Existent/File.php';
        $this->setExpectedException('Laminas\Code\Reflection\Exception\InvalidArgumentException', 'found');
        $reflectionFile = new FileReflection($nonExistentFile);
    }

    public function testFileConstructorFromAReflectedFilenameInIncludePathWithoutIncludeFlagEnabled()
    {
        $this->setExpectedException('Laminas\Code\Reflection\Exception\RuntimeException', 'must be required');
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
            new FileReflection('an_empty_file.php', false);
            set_include_path($oldIncludePath);
        } catch (Exception $e) {
            set_include_path($oldIncludePath);
            throw $e;
        }
    }

    public function testFileConstructorFromAReflectedFilenameInIncludePath()
    {
        $this->assertNotContains(realpath(__DIR__ . '/TestAsset/a_second_empty_file.php'), get_included_files());
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
        $this->assertEquals(get_class($reflectionFile), 'Laminas\Code\Reflection\FileReflection');
        $this->assertEquals(count($reflectionFile->getClasses()), 1);
    }

    public function testFileGetClassReturnsFirstClassWithNoOptions()
    {
        $fileToReflect = __DIR__ . '/TestAsset/TestSampleClass.php';
        include_once $fileToReflect;
        $reflectionFile = new FileReflection($fileToReflect);
        $this->assertEquals(
            'LaminasTest\Code\Reflection\TestAsset\TestSampleClass',
            $reflectionFile->getClass()->getName()
        );
    }

    public function testFileGetClassThrowsExceptionOnNonExistentClassName()
    {
        $fileToReflect = __DIR__ . '/TestAsset/TestSampleClass.php';
        include_once $fileToReflect;
        $reflectionFile = new FileReflection($fileToReflect);
        $nonExistentClass = 'Some_Non_Existent_Class';

        $this->setExpectedException(
            'Laminas\Code\Reflection\Exception\InvalidArgumentException',
            'Class by name Some_Non_Existent_Class not found'
        );
        $reflectionFile->getClass($nonExistentClass);
    }

    public function testFileReflectorRequiredFunctionsDoNothing()
    {
        $this->assertNull(FileReflection::export());

        $reflectionFile = new FileReflection(__FILE__);
        $this->assertEquals('', $reflectionFile->__toString());
    }

    public function testFileGetFilenameReturnsCorrectFilename()
    {
        $reflectionFile = new FileReflection(__FILE__);

        $this->assertEquals('FileReflectionTest.php', $reflectionFile->getFileName());
    }

    public function testFileGetLineNumbersWorks()
    {
        $this->markTestIncomplete('Line numbering not implemented yet');

        $fileToReflect = __DIR__ . '/TestAsset/TestSampleClass.php';
        include_once $fileToReflect;
        $reflectionFile = new FileReflection($fileToReflect);
        $this->assertEquals(9, $reflectionFile->getStartLine());
        $this->assertEquals(24, $reflectionFile->getEndLine());
    }

    public function testFileGetDocBlockReturnsFileDocBlock()
    {
        $fileToReflect = __DIR__ . '/TestAsset/TestSampleClass7.php';
        include_once $fileToReflect;
        $reflectionFile = new FileReflection($fileToReflect);

        $reflectionDocBlock = $reflectionFile->getDocBlock();
        $this->assertInstanceOf('Laminas\Code\Reflection\DocBlockReflection', $reflectionDocBlock);

        $authorTag = $reflectionDocBlock->getTag('author');
        $this->assertEquals('Jeremiah Small', $authorTag->getAuthorName());
        $this->assertEquals('jsmall@soliantconsulting.com', $authorTag->getAuthorEmail());
    }

    public function testFileGetFunctionsReturnsFunctions()
    {
        $this->markTestIncomplete('Function scanning not implemented yet');

        $fileToRequire = __DIR__ . '/TestAsset/FileOfFunctions.php';
        include_once $fileToRequire;
        $reflectionFile = new FileReflection($fileToRequire);
        $funcs = $reflectionFile->getFunctions();
        $this->assertInstanceOf('Laminas\Code\Reflection\FunctionReflection', current($funcs));
    }

    public function testFileCanReflectFileWithInterface()
    {
        $fileToReflect = __DIR__ . '/TestAsset/TestSampleInterface.php';
        include_once $fileToReflect;
        $reflectionFile = new FileReflection($fileToReflect);
        $class = $reflectionFile->getClass();
        $this->assertEquals('LaminasTest\Code\Reflection\TestAsset\TestSampleInterface', $class->getName());
        $this->assertTrue($class->isInterface());
    }

    public function testFileCanReflectFileWithUses()
    {
        $fileToReflect = __DIR__ . '/TestAsset/TestSampleClass8.php';
        include_once $fileToReflect;
        $reflectionFile = new FileReflection($fileToReflect);
        $expected = [
            ['use' => 'Laminas\Config', 'as' => 'LaminasConfig'],
            ['use' => 'FooBar\Foo\Bar', 'as' => null],
            ['use' => 'One\Two\Three\Four\Five', 'as' => 'ottff']
            ];
        $this->assertSame($expected, $reflectionFile->getUses());
    }

    /**
     * @group 70
     * @group 43
     */
    public function testFileReflectionShouldNotRaiseNoticesWhenReflectingClosures()
    {
        require_once __DIR__ . '/TestAsset/issue-70.php';
        $r = new FileReflection(__DIR__ . '/TestAsset/issue-70.php');
        $this->assertContains('spl_autoload_register', $r->getContents());
        $this->assertContains('function ()', $r->getContents());
    }
}
