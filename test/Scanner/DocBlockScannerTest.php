<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Scanner;

use Laminas\Code\Scanner\DocBlockScanner;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @category   Laminas
 * @package    Laminas_Code_Scanner
 * @subpackage UnitTests
 * @group      Laminas_Code_Scanner
 */
class DocBlockScannerTest extends TestCase
{
    /**
     * @group Laminas-110
     */
    public function testDocBlockScannerParsesTagsWithNoValuesProperly()
    {
        $docComment = <<<EOB
/**
 * @mytag
 */
EOB;
        $tokenScanner = new DocBlockScanner($docComment);
        $tags = $tokenScanner->getTags();
        $this->assertCount(1, $tags);
        $this->assertArrayHasKey('name', $tags[0]);
        $this->assertEquals('@mytag', $tags[0]['name']);
        $this->assertArrayHasKey('value', $tags[0]);
        $this->assertEquals('', $tags[0]['value']);
    }

    public function testDocBlockScannerDescriptions()
    {
        $docComment = <<<EOB
/**
 * Short Description
 *
 * Long Description
 * continued in the second line
 */
EOB;
        $tokenScanner = new DocBlockScanner($docComment);
        $this->assertEquals('Short Description', $tokenScanner->getShortDescription());
        $this->assertEquals('Long Description continued in the second line', $tokenScanner->getLongDescription());

        // windows-style line separators
        $docComment = str_replace("\n", "\r\n", $docComment);
        $tokenScanner = new DocBlockScanner($docComment);
        $this->assertEquals('Short Description', $tokenScanner->getShortDescription());
        $this->assertEquals('Long Description continued in the second line', $tokenScanner->getLongDescription());
    }
}
