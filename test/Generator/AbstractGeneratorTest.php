<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator;

/**
 * @category   Laminas
 * @package    Laminas_Code_Generator
 * @subpackage UnitTests
 *
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class AbstractGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $generator = $this->getMockForAbstractClass('Laminas\Code\Generator\AbstractGenerator', array(
            array(
                'indentation' => 'foo',
            )
        ));

        $this->assertInstanceOf('Laminas\Code\Generator\GeneratorInterface', $generator);
        $this->assertEquals('foo', $generator->getIndentation());
    }
}
