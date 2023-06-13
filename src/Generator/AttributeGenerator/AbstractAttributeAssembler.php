<?php

declare(strict_types=1);

namespace Laminas\Code\Generator\AttributeGenerator;

use ReflectionAttribute;

abstract class AbstractAttributeAssembler implements AttributeAssembler
{
    public function __construct(private ReflectionAttribute $attributePrototype)
    {
    }

    final protected function getName(): string
    {
        return $this->attributePrototype->getName();
    }

    final protected function getArguments(): array
    {
        return $this->attributePrototype->getArguments();
    }
}
