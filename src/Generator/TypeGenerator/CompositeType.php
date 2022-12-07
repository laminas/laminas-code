<?php

namespace Laminas\Code\Generator\TypeGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;

use function array_diff_key;
use function array_filter;
use function array_flip;
use function array_map;
use function assert;
use function explode;
use function implode;
use function sprintf;
use function str_contains;
use function str_ends_with;
use function str_starts_with;
use function substr;
use function usort;

/**
 * Represents a union/intersection type, as supported by PHP.
 * This means that this object can be composed of {@see AtomicType} or other {@see CompositeType} objects.
 *
 * @internal the {@see CompositeType} is an implementation detail of the type generator,
 *
 * @psalm-immutable
 */
final class CompositeType implements TypeInterface
{
    public const UNION_SEPARATOR        = '|';
    public const INTERSECTION_SEPARATOR = '&';

    /**
     * @param non-empty-list<TypeInterface> $types
     */
    private function __construct(protected readonly array $types, private readonly bool $isIntersection)
    {
    }

    /** @psalm-pure */
    public static function fromString(string $type): self
    {
        $types          = [];
        $isIntersection = false;
        $separator      = self::UNION_SEPARATOR;

        if (! str_contains($type, $separator)) {
            $isIntersection = true;
            $separator      = self::INTERSECTION_SEPARATOR;
        }

        foreach (explode($separator, $type) as $typeString) {
            if (str_contains($typeString, self::INTERSECTION_SEPARATOR)) {
                if (! str_starts_with($typeString, '(')) {
                    throw new InvalidArgumentException(sprintf(
                        'Invalid intersection type "%s": missing opening parenthesis',
                        $typeString
                    ));
                }

                if (! str_ends_with($typeString, ')')) {
                    throw new InvalidArgumentException(sprintf(
                        'Invalid intersection type "%s": missing closing parenthesis',
                        $typeString
                    ));
                }

                $types[] = self::fromString(substr($typeString, 1, -1));
            } else {
                $types[] = AtomicType::fromString($typeString);
            }
        }

        usort(
            $types,
            static function (TypeInterface $left, TypeInterface $right): int {
                if ($left instanceof AtomicType && $right instanceof AtomicType) {
                    return [$left->sortIndex, $left->type] <=> [$right->sortIndex, $right->type];
                }

                return [$right instanceof self] <=> [$left instanceof self];
            }
        );

        foreach ($types as $index => $typeItem) {
            if (! $typeItem instanceof AtomicType) {
                continue;
            }

            $otherTypes = array_diff_key($types, array_flip([$index]));

            assert([] !== $otherTypes, 'There are always 2 or more types in a union type');

            $otherTypes = array_filter($otherTypes, static fn (TypeInterface $type) => ! $type instanceof self);

            if ([] === $otherTypes) {
                continue;
            }

            if ($isIntersection) {
                $typeItem->assertCanIntersectWith($otherTypes);
            } else {
                $typeItem->assertCanUnionWith($otherTypes);
            }
        }

        return new self($types, $isIntersection);
    }

    /**
     * @return non-empty-list<TypeInterface>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function isIntersection(): bool
    {
        return $this->isIntersection;
    }

    /** @return self::INTERSECTION_SEPARATOR|self::UNION_SEPARATOR */
    public function getSeparator(): string
    {
        return $this->isIntersection ? self::INTERSECTION_SEPARATOR : self::UNION_SEPARATOR;
    }

    public function __toString(): string
    {
        $typesAsStrings = array_map(
            static function (TypeInterface $type): string {
                $typeString = $type->__toString();

                return $type instanceof self && $type->isIntersection() ? sprintf('(%s)', $typeString) : $typeString;
            },
            $this->types
        );

        return implode($this->getSeparator(), $typesAsStrings);
    }

    public function fullyQualifiedName(): string
    {
        $typesAsStrings = array_map(
            static function (TypeInterface $type): string {
                $typeString = $type->fullyQualifiedName();

                return $type instanceof self && $type->isIntersection() ? sprintf('(%s)', $typeString) : $typeString;
            },
            $this->types
        );

        return implode($this->getSeparator(), $typesAsStrings);
    }
}
