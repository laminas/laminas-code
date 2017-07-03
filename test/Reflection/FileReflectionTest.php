<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Reflection;

use Exception;
use PHPUnit\Framework\TestCase;
use Zend\Code\Reflection\DocBlockReflection;
use Zend\Code\Reflection\Exception\InvalidArgumentException;
use Zend\Code\Reflection\Exception\RuntimeException;
use Zend\Code\Reflection\FileReflection;
use Zend\Code\Reflection\FunctionReflection;

/**
 * @group      Zend_Reflection
 * @group      Zend_Reflection_File
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
        $this->assertEquals(get_class($reflectionFile), FileReflection::class);
        $this->assertCount(1, $reflectionFile->getClasses());
    }

    public function testFileGetClassReturnsFirstClassWithNoOptions()
    {
        $fileToReflect = __DIR__ . '/TestAsset/TestSampleClass.php';
        include_once $fileToReflect;
        $reflectionFile = new FileReflection($fileToReflect);
        $this->assertEquals(TestAsset\TestSampleClass::class, $reflectionFile->getClass()->getName());
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
        $this->assertInstanceOf(DocBlockReflection::class, $reflectionDocBlock);

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
        $this->assertInstanceOf(FunctionReflection::class, current($funcs));
    }

    public function testFileCanReflectFileWithInterface()
    {
        $fileToReflect = __DIR__ . '/TestAsset/TestSampleInterface.php';
        include_once $fileToReflect;
        $reflectionFile = new FileReflection($fileToReflect);
        $class = $reflectionFile->getClass();
        $this->assertEquals(TestAsset\TestSampleInterface::class, $class->getName());
        $this->assertTrue($class->isInterface());
    }

    public function testFileCanReflectFileWithUses()
    {
        $fileToReflect = __DIR__ . '/TestAsset/TestSampleClass8.php';
        include_once $fileToReflect;
        $reflectionFile = new FileReflection($fileToReflect);
        $expected = [
            ['use' => 'Zend\Config', 'as' => 'ZendConfig'],
            ['use' => 'FooBar\Foo\Bar', 'as' => null],
            ['use' => 'One\Two\Three\Four\Five', 'as' => 'ottff'],
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
