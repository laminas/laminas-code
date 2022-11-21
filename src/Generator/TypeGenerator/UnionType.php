<?php

namespace Laminas\Code\Generator\TypeGenerator;

use function implode;
use function sprintf;

/**
 * Represents an union type, as supported by PHP.
 * This means that this object can contain multiple atomic or intersection types.
 *
 * @internal the {@see UnionType} is an implementation detail of the type generator,
 *
 * @psalm-immutable
 */
final class UnionType extends BaseComplexType
{
    public static function fromString(string $type): self
    {
        $typesAsStrings = explode(self::getSeparator(), $type);
        $types = [];

        foreach ($typesAsStrings as $typeString) {
            if (str_contains($typeString, IntersectionType::getSeparator())) {
                $types[] = IntersectionType::fromString($typeString);
            } else {
                $types[] = AtomicType::fromString($typeString);
            }
        }

        return new self($types);
    }

    public static function getSeparator(): string
    {
        return '|';
    }

    public function __toString(): string
    {
        $typesAsStrings = array_map(
            static function (Type $type): string {
                $typeString = $type->__toString();

                return $type instanceof IntersectionType ? sprintf('(%s)', $typeString) : $typeString;
            },
            $this->types
        );

        return implode(self::getSeparator(), $typesAsStrings);
    }
}
