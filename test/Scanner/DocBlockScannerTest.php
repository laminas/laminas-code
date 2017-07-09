<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Scanner;

use PHPUnit\Framework\TestCase;
use Zend\Code\Scanner\DocBlockScanner;

use function str_replace;

/**
 * @group      Zend_Code_Scanner
 */
class DocBlockScannerTest extends TestCase
{
    /**
     * @group ZF2-110
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
        self::assertCount(1, $tags);
        self::assertArrayHasKey('name', $tags[0]);
        self::assertEquals('@mytag', $tags[0]['name']);
        self::assertArrayHasKey('value', $tags[0]);
        self::assertEquals('', $tags[0]['value']);
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
        self::assertEquals('Short Description', $tokenScanner->getShortDescription());
        self::assertEquals('Long Description continued in the second line', $tokenScanner->getLongDescription());

        // windows-style line separators
        $docComment = str_replace("\n", "\r\n", $docComment);
        $tokenScanner = new DocBlockScanner($docComment);
        self::assertEquals('Short Description', $tokenScanner->getShortDescription());
        self::assertEquals('Long Description continued in the second line', $tokenScanner->getLongDescription());
    }
}
