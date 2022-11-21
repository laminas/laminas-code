<?php

declare(strict_types=1);

namespace LaminasTest\Code\Generator;

use Laminas\Code\Generator\AttributeGenerator;
use Laminas\Code\Generator\AttributeGenerator\AttributePrototype;
use PHPUnit\Framework\TestCase;

final class AttributeGeneratorByPrototypeTest extends TestCase
{
    /**
     * @test
     */
    public function generate_single_attribute(): void
    {
        $prototype = new AttributePrototype('LaminasTest\Code\Generator\Fixture\AttributeGenerator\SimpleAttribute');
        $generator = $this->giveGenerator($prototype);

        $result = $generator->generate();

        $expectedResult = '#[LaminasTest\Code\Generator\Fixture\AttributeGenerator\SimpleAttribute]';
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function generate_many_single_attributes(): void
    {
        $prototype1 = new AttributePrototype('LaminasTest\Code\Generator\Fixture\AttributeGenerator\SimpleAttribute');
        $prototype2 = new AttributePrototype('LaminasTest\Code\Generator\Fixture\AttributeGenerator\SimpleAttribute');
        $generator = $this->giveGenerator($prototype1, $prototype2);

        $result = $generator->generate();

        $expectedResult = "#[LaminasTest\Code\Generator\Fixture\AttributeGenerator\SimpleAttribute]\n#[LaminasTest\Code\Generator\Fixture\AttributeGenerator\SimpleAttribute]";
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function generate_single_attribute_with_arguments(): void
    {
        $prototype = new AttributePrototype(
            'LaminasTest\Code\Generator\Fixture\AttributeGenerator\AttributeWithArguments',
            [
                'boolArgument' => false,
                'stringArgument' => 'char chain',
                'intArgument' => 16,
            ],
        );
        $generator = $this->giveGenerator($prototype);

        $result = $generator->generate();

        $expectedResult = "#[LaminasTest\Code\Generator\Fixture\AttributeGenerator\AttributeWithArguments(boolArgument: false, stringArgument: 'char chain', intArgument: 16)]";
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function generate_many_attributes_with_arguments(): void
    {
        $prototype1 = new AttributePrototype(
            'LaminasTest\Code\Generator\Fixture\AttributeGenerator\AttributeWithArguments',
            [
                'boolArgument' => false,
                'stringArgument' => 'char chain',
                'intArgument' => 16,
            ],
        );
        $prototype2 = new AttributePrototype('LaminasTest\Code\Generator\Fixture\AttributeGenerator\AttributeWithArguments');
        $generator = $this->giveGenerator($prototype1, $prototype2);

        $result = $generator->generate();

        $expectedResult = "#[LaminasTest\Code\Generator\Fixture\AttributeGenerator\AttributeWithArguments(boolArgument: false, stringArgument: 'char chain', intArgument: 16)]\n#[LaminasTest\Code\Generator\Fixture\AttributeGenerator\AttributeWithArguments]";
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function mix_simple_attributes_with_attributes_with_arguments(): void
    {
        $prototype1 = new AttributePrototype(
            'LaminasTest\Code\Generator\Fixture\AttributeGenerator\AttributeWithArguments', 
            [
            'stringArgument' => 'any string',
            'intArgument' => 1,
            'boolArgument' => true,
        ]);
        $prototype2 = new AttributePrototype('LaminasTest\Code\Generator\Fixture\AttributeGenerator\SimpleAttribute');
        $generator = $this->giveGenerator($prototype1, $prototype2);

        $result = $generator->generate();

        $expectedResult = "#[LaminasTest\Code\Generator\Fixture\AttributeGenerator\AttributeWithArguments(stringArgument: 'any string', intArgument: 1, boolArgument: true)]\n#[LaminasTest\Code\Generator\Fixture\AttributeGenerator\SimpleAttribute]";
        $this->assertSame($expectedResult, $result);
    }

    private function giveGenerator(AttributePrototype ...$prototype): AttributeGenerator
    {
        return AttributeGenerator::fromPrototype(...$prototype);
    }
}
