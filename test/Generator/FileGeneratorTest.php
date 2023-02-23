<?php

namespace LaminasTest\Code\Generator;

use Laminas\Code\DeclareStatement;
use Laminas\Code\Exception\InvalidArgumentException;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\Exception\ClassNotFoundException;
use Laminas\Code\Generator\FileGenerator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use stdClass;

use function explode;
use function file_get_contents;
use function get_class;
use function strlen;
use function strpos;
use function strrpos;
use function sys_get_temp_dir;

#[Group('Laminas_Code_Generator')]
#[Group('Laminas_Code_Generator_Php')]
#[Group('Laminas_Code_Generator_Php_File')]
class FileGeneratorTest extends TestCase
{
    public function testConstruction()
    {
        $file = new FileGenerator();
        self::assertSame(FileGenerator::class, $file::class);
    }

    public function testSourceContentGetterAndSetter()
    {
        $file = new FileGenerator();
        $file->setSourceContent('Foo');
        self::assertSame('Foo', $file->getSourceContent());
    }

    public function testIndentationGetterAndSetter()
    {
        $file = new FileGenerator();
        $file->setIndentation('        ');
        self::assertSame('        ', $file->getIndentation());
    }

    public function testToString()
    {
        $codeGenFile = FileGenerator::fromArray([
            'requiredFiles' => ['SampleClass.php'],
            'class'         => [
                'flags'                 => ClassGenerator::FLAG_ABSTRACT,
                'name'                  => 'SampleClass',
                'extendedClass'         => 'ExtendedClassName',
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
        self::assertSame($expectedOutput, $output, $output);
    }

    public function testClassNotFoundException()
    {
        $fileGenerator = new FileGenerator();

        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage('No class is set');
        $fileGenerator->getClass();

        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage('Class TestClass is not set');
        $fileGenerator->getClass('TestClass');
    }

    #[Group('test')]
    public function testFileLineEndingsAreAlwaysLineFeed()
    {
        $codeGenFile = FileGenerator::fromArray([
            'requiredFiles' => ['SampleClass.php'],
            'class'         => [
                'abstract'              => true,
                'name'                  => 'SampleClass',
                'extendedClass'         => 'ExtendedClassName',
                'implementedInterfaces' => ['Iterator', 'Traversable'],
            ],
        ]);

        // explode by newline, this would leave CF in place if it were generated
        $lines = explode("\n", $codeGenFile->generate());

        $targetLength = strlen('require_once \'SampleClass.php\';');
        self::assertSame($targetLength, strlen($lines[2]));
        self::assertSame(';', $lines[2][$targetLength - 1]);
    }

    #[Group('Laminas-11218')]
    public function testGeneratesUseStatements()
    {
        $file = new FileGenerator();
        $file->setUse('My\Baz')
             ->setUses([
                 ['use' => 'Your\Bar', 'as' => 'bar'],
             ]);
        $generated = $file->generate();
        self::assertStringContainsString('use My\\Baz;', $generated);
        self::assertStringContainsString('use Your\\Bar as bar;', $generated);
    }

    public function testGeneratesNamespaceStatements()
    {
        $file = new FileGenerator();
        $file->setNamespace('Foo\Bar');
        $generated = $file->generate();
        self::assertStringContainsString('namespace Foo\\Bar', $generated, $generated);
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
        self::assertStringContainsString('use Your\\Bar as bar;', $generated);
        self::assertStringContainsString('use Your\\Bar as bar2;', $generated);
    }

    public function testSetUsesWithArrays()
    {
        $file = new FileGenerator();
        $file->setUses([
            ['use' => 'Your\\Bar', 'as' => 'bar'],
            ['use' => 'My\\Baz', 'as' => 'FooBaz'],
        ]);
        $generated = $file->generate();
        self::assertStringContainsString('use My\\Baz as FooBaz;', $generated);
        self::assertStringContainsString('use Your\\Bar as bar;', $generated);
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
        self::assertStringContainsString('use My\\Baz;', $generated);
        self::assertStringContainsString('use Your\\Bar;', $generated);
        self::assertStringContainsString('use Another\\Baz as Baz2;', $generated);
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
        self::assertStringContainsString('use My\\Baz;', $generated);
        self::assertStringContainsString('use Your\\Bar;', $generated);
        self::assertStringContainsString('use Another\\Baz as Baz2;', $generated);
    }

    public function testCreateFromArrayWithClassInstance()
    {
        $fileGenerator = FileGenerator::fromArray([
            'filename' => 'foo.php',
            'class'    => new ClassGenerator('bar'),
        ]);
        $class         = $fileGenerator->getClass('bar');
        self::assertInstanceOf(ClassGenerator::class, $class);
    }

    public function testCreateFromArrayWithClassFromArray()
    {
        $fileGenerator = FileGenerator::fromArray([
            'filename' => 'foo.php',
            'class'    => [
                'name' => 'bar',
            ],
        ]);
        $class         = $fileGenerator->getClass('bar');
        self::assertInstanceOf(ClassGenerator::class, $class);
    }

    public function testSingleDeclareStatement(): void
    {
        $generator = FileGenerator::fromArray([
            'declares' => [
                'strict_types' => 1,
            ],
            'class'    => [
                'name' => 'SampleClass',
            ],
        ]);
        $generator->setFilename(sys_get_temp_dir() . '/result_file.php');
        $generator->write();

        $expected = <<<EOS
<?php

declare(strict_types=1);

class SampleClass
{
}


EOS;

        $actual = file_get_contents(sys_get_temp_dir() . '/result_file.php');
        $this->assertSame($expected, $actual);
    }

    public function testMultiDeclareStatements(): void
    {
        $generator = FileGenerator::fromArray([
            'declares' => [
                'strict_types' => 1,
                'ticks'        => 2,
            ],
            'class'    => [
                'name' => 'SampleClass',
            ],
        ]);
        $generator->setFilename(sys_get_temp_dir() . '/result_file.php');
        $generator->write();

        $expected = <<<EOS
<?php

declare(strict_types=1);
declare(ticks=2);

class SampleClass
{
}


EOS;

        $actual = file_get_contents(sys_get_temp_dir() . '/result_file.php');
        $this->assertSame($expected, $actual);
    }

    public function testDeclareUnknownDirectiveShouldRaiseException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Declare directive must be one of: ticks, strict_types, encoding.');

        FileGenerator::fromArray([
            'declares' => [
                'fubar' => 1,
            ],
            'class'    => [
                'name' => 'SampleClass',
            ],
        ]);
    }

    public function testDeclareWrongTypeShouldRaiseException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Declare value invalid. Expected integer, got string.');

        FileGenerator::fromArray([
            'declares' => [
                'strict_types' => 'wrong type',
            ],
            'class'    => [
                'name' => 'SampleClass',
            ],
        ]);
    }

    public function testDeclareDuplicatesShouldOnlyGenerateOne(): void
    {
        $generator = FileGenerator::fromArray([
            'class' => [
                'name' => 'SampleClass',
            ],
        ]);
        $generator->setFilename(sys_get_temp_dir() . '/result_file.php');
        $generator->setDeclares([
            DeclareStatement::strictTypes(1),
            DeclareStatement::strictTypes(2),
        ]);
        $generator->write();

        $expected = <<<EOS
<?php

declare(strict_types=1);

class SampleClass
{
}


EOS;

        $actual = file_get_contents(sys_get_temp_dir() . '/result_file.php');
        $this->assertSame($expected, $actual);
    }

    public function testWrongDeclareTypeShouldRaiseException(): void
    {
        $generator = new FileGenerator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('setDeclares is expecting an array of Laminas\\Code\\DeclareStatement objects');
        /** @psalm-suppress InvalidArgument */
        $generator->setDeclares([new stdClass()]);
    }

    #[Group('gh-42')]
    public function testDeclareStatementsArePutBeforeNamespace(): void
    {
        $generator = new FileGenerator();

        $generator->setNamespace('Foo');
        $generator->setDeclares([
            DeclareStatement::strictTypes(1),
        ]);

        self::assertStringMatchesFormat('%Adeclare(strict_types=1);%Anamespace Foo;%A', $generator->generate());
    }
}
