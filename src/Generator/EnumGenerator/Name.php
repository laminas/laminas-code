<?php

namespace Laminas\Code\Generator\EnumGenerator;

use function strrpos;
use function substr;

/**
 * @internal
 *
 * @psalm-immutable
 */
final class Name
{
    private string $name;

    private ?string $namespace;

    private function __construct(string $name, ?string $namespace)
    {
        $this->name      = $name;
        $this->namespace = $namespace;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public static function fromFullyQualifiedClassName(string $name): self
    {
        $namespace  = null;
        $nsPosition = strrpos($name, '\\');
        if (false !== $nsPosition) {
            $namespace = substr($name, 0, $nsPosition);
            $name      = substr($name, $nsPosition + 1);
        }

        return new self($name, $namespace);
    }
}
