<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator;

use Laminas\Code\Generator\AbstractGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\GeneratorInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class AbstractGeneratorTest extends TestCase
{
    public function testConstructor()
    {
        $generator = $this->getMockForAbstractClass(AbstractGenerator::class, [
            [
                'indentation' => 'foo',
            ],
        ]);

        self::assertInstanceOf(GeneratorInterface::class, $generator);
        self::assertSame('foo', $generator->getIndentation());
    }

    public function testSetOptionsThrowsExceptionOnInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->getMockForAbstractClass(AbstractGenerator::class, ['sss']);
    }
}
