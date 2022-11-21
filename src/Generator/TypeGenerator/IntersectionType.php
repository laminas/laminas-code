<?php

namespace Laminas\Code\Generator\TypeGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;

use function implode;
use function sprintf;
use function substr;

/**
 * Represents an intersection type, as supported by PHP.
 * This means that this object can contain multiple atomic types.
 *
 * @internal the {@see IntersectionType} is an implementation detail of the type generator,
 *
 * @psalm-immutable
 */
final class IntersectionType extends BaseComplexType
{
    public static function fromString(string $type): self
    {
        if (str_starts_with($type, '(')) {
            if (!str_ends_with($type, ')')) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid intersection type "%s": missing closing parenthesis',
                    $type
                ));
            }
            $type = substr($type, 1, -1);
        }

        $types = array_map([AtomicType::class, 'fromString'], explode(self::getSeparator(), $type));

        return new self($types);
    }

    public static function getSeparator(): string
    {
        return '&';
    }

    public function __toString(): string
    {
        $typesAsStrings = array_map(
            static fn (AtomicType $type): string => $type->fullyQualifiedName(),
            $this->types
        );

        return implode(self::getSeparator(), $typesAsStrings);
    }
}
