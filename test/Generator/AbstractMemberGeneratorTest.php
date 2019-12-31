<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator;

use Laminas\Code\Generator\AbstractMemberGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;

class AbstractMemberGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractMemberGenerator
     */
    private $fixture;

    protected function setUp()
    {
        $this->fixture = $this->getMockForAbstractClass('Laminas\Code\Generator\AbstractMemberGenerator');
    }

    public function testSetFlagsWithArray()
    {
        $this->fixture->setFlags(
            [
                AbstractMemberGenerator::FLAG_FINAL,
                AbstractMemberGenerator::FLAG_PUBLIC,
            ]
        );

        $this->assertEquals(AbstractMemberGenerator::VISIBILITY_PUBLIC, $this->fixture->getVisibility());
        $this->assertEquals(true, $this->fixture->isFinal());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetDocBlockThrowsExceptionWithInvalidType()
    {
        $this->fixture->setDocBlock(new \stdClass());
    }
}
