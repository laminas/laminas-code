<?php

declare(strict_types=1);

namespace LaminasTest\Code\Generator;

use Laminas\Code\Generator\AttributeGenerator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class AttributeGeneratorByBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function generate_single_attribute(): void
    {
        $builder = new AttributeGenerator\AttributeBuilder();
        $builder->add('LaminasTest\Code\Generator\Fixture\AttributeGenerator\SimpleAttribute');
        $generator = $this->giveGenerator($builder);

        $result = $generator->generate();

        $expectedResult = '#[LaminasTest\Code\Generator\Fixture\AttributeGenerator\SimpleAttribute]';
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function generate_many_single_attributes(): void
    {
        $builder = new AttributeGenerator\AttributeBuilder();
        $builder->add('LaminasTest\Code\Generator\Fixture\AttributeGenerator\SimpleAttribute');
        $builder->add('LaminasTest\Code\Generator\Fixture\AttributeGenerator\SimpleAttribute');
        $generator = $this->giveGenerator($builder);

        $result = $generator->generate();

        $expectedResult = "#[LaminasTest\Code\Generator\Fixture\AttributeGenerator\SimpleAttribute]\n#[LaminasTest\Code\Generator\Fixture\AttributeGenerator\SimpleAttribute]";
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function generate_single_attribute_with_arguments(): void
    {
        $builder = new AttributeGenerator\AttributeBuilder();
        $builder->add('LaminasTest\Code\Generator\Fixture\AttributeGenerator\AttributeWithArguments', [
            'boolArgument' => false,
            'stringArgument' => 'char chain',
            'intArgument' => 16,
        ]);
        $generator = $this->giveGenerator($builder);

        $result = $generator->generate();

        $expectedResult = "#[LaminasTest\Code\Generator\Fixture\AttributeGenerator\AttributeWithArguments(boolArgument: false, stringArgument: 'char chain', intArgument: 16)]";
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function generate_many_attributes_with_arguments(): void
    {
        $builder = new AttributeGenerator\AttributeBuilder();
        $builder
            ->add('LaminasTest\Code\Generator\Fixture\AttributeGenerator\AttributeWithArguments', [
            'boolArgument' => false,
            'stringArgument' => 'char chain',
            'intArgument' => 16,
        ])
            ->add('LaminasTest\Code\Generator\Fixture\AttributeGenerator\AttributeWithArguments');
        $generator = $this->giveGenerator($builder);

        $result = $generator->generate();

        $expectedResult = "#[LaminasTest\Code\Generator\Fixture\AttributeGenerator\AttributeWithArguments(boolArgument: false, stringArgument: 'char chain', intArgument: 16)]\n#[LaminasTest\Code\Generator\Fixture\AttributeGenerator\AttributeWithArguments]";
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function mix_simple_attributes_with_attributes_with_arguments(): void
    {
        $builder = new AttributeGenerator\AttributeBuilder();
        $builder
            ->add('LaminasTest\Code\Generator\Fixture\AttributeGenerator\AttributeWithArguments', [
                'stringArgument' => 'any string',
                'intArgument' => 1,
                'boolArgument' => true,
            ])
            ->add('LaminasTest\Code\Generator\Fixture\AttributeGenerator\SimpleAttribute');
        $generator = $this->giveGenerator($builder);

        $result = $generator->generate();

        $expectedResult = "#[LaminasTest\Code\Generator\Fixture\AttributeGenerator\AttributeWithArguments(stringArgument: 'any string', intArgument: 1, boolArgument: true)]\n#[LaminasTest\Code\Generator\Fixture\AttributeGenerator\SimpleAttribute]";
        $this->assertSame($expectedResult, $result);
    }

    private function giveGenerator(AttributeGenerator\AttributeBuilder $builder): AttributeGenerator
    {
        return AttributeGenerator::fromBuilder($builder);
    }
}
