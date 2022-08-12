<?php

declare(strict_types=1);

namespace Laminas\Code\Generator;

use Laminas\Code\Generator\AttributeGenerator\AttributeAssembler;
use Laminas\Code\Generator\AttributeGenerator\AttributeAssemblerBag;
use Laminas\Code\Generator\AttributeGenerator\AttributeAssemblerFactory;
use Laminas\Code\Generator\AttributeGenerator\AttributeBuilder;
use ReflectionClass;

class AttributeGenerator extends AbstractGenerator
{
    private function __construct(private AttributeAssemblerBag $attributeAssemblerBag)
    {
    }

    public function generate(): string
    {
        $generatedAttributes = array_map(fn(AttributeAssembler $attributeAssembler) => $attributeAssembler->__toString(),
            $this->attributeAssemblerBag->toArray(),
        );

        return implode(self::LINE_FEED, $generatedAttributes);
    }

    public static function fromReflection(ReflectionClass $reflectionClass): self
    {
        return new self(AttributeAssemblerFactory::createForClassByReflection($reflectionClass));
    }

    public static function fromBuilder(AttributeBuilder $attributeBuilder): self
    {
        return new self(AttributeAssemblerFactory::createForClassFromBuilder($attributeBuilder));
    }

    public static function fromArray(array $definitions): self
    {
        $builder = new AttributeBuilder();

        foreach ($definitions as $definition) {
            @list($attributeName, $attributeArguments) = $definition;

            if (!isset($attributeArguments)) {
                $attributeArguments = [];
            }

            $builder->add($attributeName, $attributeArguments);
        }

        return self::fromBuilder($builder);
    }
}
