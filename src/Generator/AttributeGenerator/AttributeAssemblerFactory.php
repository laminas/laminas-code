<?php

declare(strict_types=1);

namespace Laminas\Code\Generator\AttributeGenerator;

use ReflectionAttribute;
use ReflectionClass;

final class AttributeAssemblerFactory
{
    public static function createForClassByReflection(ReflectionClass $reflectionClass): AttributeAssemblerBag
    {
        $attributes = $reflectionClass->getAttributes();
        $assemblyBag = new AttributeAssemblerBag();

        foreach ($attributes as $attribute) {
            $assembler = self::negotiateAssembler($attribute);

            $assemblyBag->add($assembler);
        }

        return $assemblyBag;
    }

    public static function createForClassFromBuilder(AttributeBuilder $attributeBuilder): AttributeAssemblerBag
    {
        $attributes = $attributeBuilder->build();
        $assemblyBag = new AttributeAssemblerBag();

        foreach ($attributes as $attribute) {
            $assembler = self::negotiateAssembler($attribute);

            $assemblyBag->add($assembler);
        }

        return $assemblyBag;
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
