<?php

declare(strict_types=1);

namespace LaminasTest\Code\Generator;

use Laminas\Code\Generator\PromotedParameterGenerator;
use Laminas\Code\Reflection\ParameterReflection;
use LaminasTest\Code\TestAsset\ClassWithPromotedProperties;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Laminas_Code_Generator')]
#[Group('Laminas_Code_Generator_Php')]
class PromotedParameterGeneratorTest extends TestCase
{
    public function testNullablePromotedProperty(): void
    {
        $parameterReflection = new ParameterReflection([ClassWithPromotedProperties::class, '__construct'], 0);

        $generator = PromotedParameterGenerator::fromReflection($parameterReflection);

        $this->assertSame('protected ?int $nullable', $generator->generate());
    }
}
