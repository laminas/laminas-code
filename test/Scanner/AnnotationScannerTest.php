<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Scanner;

use Laminas\Code\Annotation\AnnotationManager;
use Laminas\Code\Annotation\Parser\GenericAnnotationParser;
use Laminas\Code\NameInformation;
use Laminas\Code\Scanner\AnnotationScanner;
use PHPUnit\Framework\TestCase;

use function get_class;

class AnnotationScannerTest extends TestCase
{
    /**
     * @dataProvider newLine
     *
     * @param string $newLine
     */
    public function testScannerWorks($newLine)
    {
        $annotationManager = new AnnotationManager();
        $parser = new GenericAnnotationParser();
        $parser->registerAnnotations([
            $foo = new TestAsset\Annotation\Foo(),
            $bar = new TestAsset\Annotation\Bar(),
        ]);
        $annotationManager->attach($parser);

        $docComment = '/**' . $newLine
            . ' * @Test\Foo(\'anything I want()' . $newLine
            . ' * to be\')' . $newLine
            . ' * @Test\Bar' . $newLine . ' */';

        $nameInfo = new NameInformation();
        $nameInfo->addUse('LaminasTest\Code\Scanner\TestAsset\Annotation', 'Test');

        $annotationScanner = new AnnotationScanner($annotationManager, $docComment, $nameInfo);
        self::assertEquals(get_class($foo), get_class($annotationScanner[0]));
        self::assertEquals("'anything I want()\n to be'", $annotationScanner[0]->getContent());
        self::assertEquals(get_class($bar), get_class($annotationScanner[1]));
    }

    public function newLine()
    {
        return [
            ["\n"],
            ["\r"],
            ["\r\n"],
        ];
    }
}
