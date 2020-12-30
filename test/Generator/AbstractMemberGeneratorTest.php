<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator;

use Laminas\Code\Generator\AbstractMemberGenerator;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

class AbstractMemberGeneratorTest extends TestCase
{
    /** @var AbstractMemberGenerator */
    private $fixture;

    protected function setUp(): void
    {
        $this->fixture = $this->getMockForAbstractClass(AbstractMemberGenerator::class);
    }

    public function testSetFlagsWithArray()
    {
        $this->fixture->setFlags(
            [
                AbstractMemberGenerator::FLAG_FINAL,
                AbstractMemberGenerator::FLAG_PUBLIC,
            ]
        );

        self::assertEquals(AbstractMemberGenerator::VISIBILITY_PUBLIC, $this->fixture->getVisibility());
        self::assertEquals(true, $this->fixture->isFinal());
    }

    public function testSetDocBlockThrowsExceptionWithInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->fixture->setDocBlock(new stdClass());
    }

    public function testRemoveDocBlock(): void
    {
        $this->fixture->setDocBlock(new DocBlockGenerator());

        $this->fixture->removeDocBlock();

        $this->assertNull($this->fixture->getDocBlock());
    }

    public function testRemoveDocBlockIsIdempotent(): void
    {
        $this->fixture->removeDocBlock();
        $this->fixture->removeDocBlock();

        $this->assertNull($this->fixture->getDocBlock());
    }
}
