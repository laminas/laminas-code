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

class AnnotationScannerTest extends \PHPUnit_Framework_TestCase
{
    public function testScannerWorks()
    {
        $annotationManager = new AnnotationManager();
        $parser = new GenericAnnotationParser();
        $parser->registerAnnotations(array(
            $foo = new TestAsset\Annotation\Foo(),
            $bar = new TestAsset\Annotation\Bar()
        ));
        $annotationManager->attach($parser);

        $docComment = '/**' . "\n"
            . ' * @Test\Foo(\'anything I want()' . "\n" . ' * to be\')' . "\n"
            . ' * @Test\Bar' . "\n */";

        $nameInfo = new NameInformation();
        $nameInfo->addUse('LaminasTest\Code\Scanner\TestAsset\Annotation', 'Test');

        $annotationScanner = new AnnotationScanner($annotationManager, $docComment, $nameInfo);
        $this->assertEquals(get_class($foo), get_class($annotationScanner[0]));
        $this->assertEquals("'anything I want()\n to be'", $annotationScanner[0]->getContent());
        $this->assertEquals(get_class($bar), get_class($annotationScanner[1]));
    }
}
