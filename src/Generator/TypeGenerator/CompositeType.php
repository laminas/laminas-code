<?php

namespace Laminas\Code\Generator\TypeGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;

/** @internal */
class CompositeType implements Type
{
    public const UNION_SEPARATOR = '|';
    public const INTERSECTION_SEPARATOR = '&';

    /**
     * @param list<Type> $types
     */
    private function __construct(protected readonly array $types, private readonly bool $isIntersection)
    {
    }

    public static function fromString(string $type): self
    {
        $types = [];
        $isIntersection = false;
        $separator = self::UNION_SEPARATOR;

        if (!str_contains($type, $separator)) {
            $isIntersection = true;
            $separator = self::INTERSECTION_SEPARATOR;

            // Trim parenthesis for intersection types that are a part of a union type
            if (str_starts_with($type, '(')) {
                if (!str_ends_with($type, ')')) {
                    throw new InvalidArgumentException(sprintf(
                        'Invalid intersection type "%s": missing closing parenthesis',
                        $type
                    ));
                }
                $type = substr($type, 1, -1);
            }
        }

        foreach (explode($separator, $type) as $typeString) {
            if (str_contains($typeString, self::INTERSECTION_SEPARATOR)) {
                $types[] = self::fromString($typeString);
            } else {
                $types[] = AtomicType::fromString($typeString);
            }
        }

        usort(
            $types,
            static function (Type $left, Type $right): int {
                if ($left instanceof AtomicType && $right instanceof AtomicType) {
                    return [$left->sortIndex, $left->type] <=> [$right->sortIndex, $right->type];
                }

                return [$right instanceof self] <=> [$left instanceof self];
            }
        );

        foreach ($types as $index => $typeItem) {
            if (!$typeItem instanceof AtomicType) {
                continue;
            }

            $otherTypes = array_diff_key($types, array_flip([$index]));

            assert([] !== $otherTypes, 'There are always 2 or more types in a union type');

            $otherTypes = array_filter($otherTypes, static fn (Type $type) => !($type instanceof self));

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
     * @return list<Type>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function isIntersection(): bool
    {
        return $this->isIntersection;
    }

    public function getSeparator(): string
    {
        return $this->isIntersection ? self::INTERSECTION_SEPARATOR : self::UNION_SEPARATOR;
    }

    public function __toString()
    {
        $typesAsStrings = array_map(
            static function (Type $type): string {
                $typeString = $type->__toString();

                return $type instanceof self && $type->isIntersection() ? sprintf('(%s)', $typeString) : $typeString;
            },
            $this->types
        );

        return implode($this->getSeparator(), $typesAsStrings);
    }

    public function toString(): string
    {
        $typesAsStrings = array_map(
            static function (Type $type): string {
                $typeString = $type->toString();

                return $type instanceof self && $type->isIntersection() ? sprintf('(%s)', $typeString) : $typeString;
            },
            $this->types
        );

        return implode($this->getSeparator(), $typesAsStrings);
    }
}
