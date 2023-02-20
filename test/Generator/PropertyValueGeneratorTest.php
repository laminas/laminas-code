<?php

namespace LaminasTest\Code\Generator;

use Laminas\Code\Generator\PropertyValueGenerator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Laminas_Code_Generator')]
#[Group('Laminas_Code_Generator_Php')]
class PropertyValueGeneratorTest extends TestCase
{
    public function testPropertyValueAddsSemicolonToValueGenerator()
    {
        $value = new PropertyValueGenerator('foo');
        self::assertSame('\'foo\';', $value->generate());
    }
}
