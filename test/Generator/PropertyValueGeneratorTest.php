<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator;

use Laminas\Code\Generator\PropertyValueGenerator;

/**
 * @category   Laminas
 * @package    Laminas_Code_Generator
 * @subpackage UnitTests
 *
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class PropertyValueGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testPropertyValueAddsSemicolonToValueGenerator()
    {
        $value = new PropertyValueGenerator('foo');
        $this->assertEquals('\'foo\';', $value->generate());
    }
}
