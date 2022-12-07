<?php

namespace Laminas\Code\Generator\TypeGenerator;

use Stringable;

/**
 * Represents a single or a composite type, as supported by PHP.
 *
 * @internal
 *
 * @psalm-immutable
 */
interface TypeInterface extends Stringable
{
    /**
     * Builds a type object from a string after validating it.
     *
     * @return static
     */
    public static function fromString(string $type): self;

    /**
     * Returns a fully-qualified string representation of the type.
     *
     * @psalm-return non-empty-string
     */
    public function fullyQualifiedName(): string;

    /** @return non-empty-string */
    public function __toString(): string;
}
