<?php

namespace LaminasTest\Code\Generator;

use Laminas\Code\Generator\PropertyValueGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class PropertyValueGeneratorTest extends TestCase
{
    public function testPropertyValueAddsSemicolonToValueGenerator()
    {
        $value = new PropertyValueGenerator('foo');
        self::assertSame('\'foo\';', $value->generate());
    }
}
