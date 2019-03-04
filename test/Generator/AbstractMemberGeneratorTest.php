<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Generator;

use PHPUnit\Framework\TestCase;
use Zend\Code\Generator\AbstractMemberGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\Exception\InvalidArgumentException;

class AbstractMemberGeneratorTest extends TestCase
{
    /**
     * @var AbstractMemberGenerator
     */
    private $fixture;

    protected function setUp()
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
        $this->fixture->setDocBlock(new \stdClass());
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
