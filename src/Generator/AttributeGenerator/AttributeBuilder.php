<?php

declare(strict_types=1);

namespace Laminas\Code\Generator\AttributeGenerator;

final class AttributeBuilder
{
    private array $definitions = [];

    public function add(string $name, array $arguments = []): self
    {
        $this->definitions[] = [$name, $arguments];

        return $this;
    }

    /**
     * @return AttributePrototype[]
     */
    public function build(): array
    {
        return array_map(function (array $definition): AttributePrototype {
            list($name, $arguments) = $definition;

            return new AttributePrototype($name, $arguments);
        }, $this->definitions);
    }
}
