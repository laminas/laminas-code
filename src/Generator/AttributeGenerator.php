<?php

declare(strict_types=1);

namespace Laminas\Code\Generator;

use Laminas\Code\Generator\AttributeGenerator\AttributeAssembler;
use Laminas\Code\Generator\AttributeGenerator\AttributePrototype;
use Laminas\Code\Generator\AttributeGenerator\AttributeWithArgumentsAssembler;
use Laminas\Code\Generator\AttributeGenerator\SimpleAttributeAssembler;
use ReflectionAttribute;
use ReflectionClass;

final class AttributeGenerator implements GeneratorInterface
{
    private array $assemblers;

    private function __construct(AttributeAssembler ...$assembler)
    {
        $this->assemblers = $assembler;
    }

    public function generate(): string
    {
        $generatedAttributes = array_map(fn(AttributeAssembler $attributeAssembler) => $attributeAssembler->assemble(),
            $this->assemblers,
        );

        return implode(AbstractGenerator::LINE_FEED, $generatedAttributes);
    }

    public static function fromPrototype(AttributePrototype ...$attributePrototype): self
    {
        $assemblers = [];
        
        foreach ($attributePrototype as $prototype) {
            $assemblers[] = self::negotiateAssembler($prototype);
        }

        return new self(...$assemblers);
    }

    public static function fromReflection(ReflectionClass $reflectionClass): self
    {
        $attributes = $reflectionClass->getAttributes();
        $assemblers = [];

        foreach ($attributes as $attribute) {
            $assembler = self::negotiateAssembler($attribute);

            $assemblers[] = $assembler;
        }

        return new self(...$assemblers);
    }

    public static function fromArray(array $definitions): self
    {
        $assemblers = [];

        foreach ($definitions as $definition) {
            @list($attributeName, $attributeArguments) = $definition;

            $prototype = new AttributePrototype($attributeName, $attributeArguments ?? []);

            $assemblers[] = self::negotiateAssembler($prototype);
        }

        return new self(...$assemblers);
    }

    private static function negotiateAssembler(ReflectionAttribute|AttributePrototype $reflectionPrototype): AttributeAssembler
    {
        $hasArguments = !empty($reflectionPrototype->getArguments());

        if ($hasArguments) {
            return new AttributeWithArgumentsAssembler($reflectionPrototype);
        }

        return new SimpleAttributeAssembler($reflectionPrototype);
    }
}
