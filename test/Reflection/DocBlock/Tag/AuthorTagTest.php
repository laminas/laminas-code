<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection\DocBlock\Tag;

use Laminas\Code\Reflection\DocBlock\Tag\AuthorTag;

/**
 * @category   Laminas
 * @package    Laminas_Reflection
 * @subpackage UnitTests
 * @group      Laminas_Reflection
 * @group      Laminas_Reflection_DocBlock
 */
class AuthorTagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AuthorTag
     */
    protected $tag;

    public function setUp()
    {
        $this->tag = new AuthorTag();
    }

    public function testParseName()
    {
        $this->tag->initialize('Firstname Lastname');
        $this->assertEquals('author', $this->tag->getName());
        $this->assertEquals('Firstname Lastname', $this->tag->getAuthorName());
    }

    public function testParseNameAndEmail()
    {
        $this->tag->initialize('Firstname Lastname <test@domain.fr>');
        $this->assertEquals('author', $this->tag->getName());
        $this->assertEquals('Firstname Lastname', $this->tag->getAuthorName());
        $this->assertEquals('test@domain.fr', $this->tag->getAuthorEmail());
    }
}
