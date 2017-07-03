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
use Zend\Code\Generator\AbstractGenerator;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\GeneratorInterface;

/**
 * @group Zend_Code_Generator
 * @group Zend_Code_Generator_Php
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

        $this->assertInstanceOf(GeneratorInterface::class, $generator);
        $this->assertEquals('foo', $generator->getIndentation());
    }

    public function testSetOptionsThrowsExceptionOnInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->getMockForAbstractClass(AbstractGenerator::class, ['sss']);
    }
}
