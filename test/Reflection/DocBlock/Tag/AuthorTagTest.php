<?php

namespace LaminasTest\Code\Reflection\DocBlock\Tag;

use Laminas\Code\Reflection\DocBlock\Tag\AuthorTag;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Laminas_Reflection')]
#[Group('Laminas_Reflection_DocBlock')]
class AuthorTagTest extends TestCase
{
    /** @var AuthorTag */
    protected $tag;

    protected function setUp(): void
    {
        $this->tag = new AuthorTag();
    }

    public function testParseName()
    {
        $this->tag->initialize('Firstname Lastname');
        self::assertEquals('author', $this->tag->getName());
        self::assertEquals('Firstname Lastname', $this->tag->getAuthorName());
    }

    public function testParseNameAndEmail()
    {
        $this->tag->initialize('Firstname Lastname <test@domain.fr>');
        self::assertEquals('author', $this->tag->getName());
        self::assertEquals('Firstname Lastname', $this->tag->getAuthorName());
        self::assertEquals('test@domain.fr', $this->tag->getAuthorEmail());
    }
}
