<?php

declare(strict_types=1);

namespace Laminas\Code\Generator\AttributeGenerator;

use ReflectionAttribute;

final class AttributePrototype extends ReflectionAttribute
{
    public function __construct(private string $attributeName, private array $arguments = [])
    {
    }

    public function getName(): string
    {
        return $this->attributeName;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }
}
