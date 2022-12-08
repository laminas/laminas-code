<?php

namespace Laminas\Code\Generator\TypeGenerator;

use function array_diff_key;
use function array_flip;
use function array_map;
use function implode;
use function usort;

/**
 * @internal the {@see UnionType} is an implementation detail of the type generator,
 *
 * @psalm-immutable
 */
final class UnionType
{
    /** @var non-empty-list<AtomicType|IntersectionType> $types sorted, at least 2 values always present */
    private readonly array $types;

    /** @param non-empty-list<AtomicType|IntersectionType> $types at least 2 values needed */
    public function __construct(array $types)
    {
        usort(
            $types,
            static fn(AtomicType|IntersectionType $a, AtomicType|IntersectionType $b): int => [
                $a instanceof IntersectionType ? -1 : $a->sortIndex,
                $a->toString(),
            ] <=> [
                $b instanceof IntersectionType ? -1 : $b->sortIndex,
                $b->toString(),
            ]
        );

        foreach ($types as $index => $type) {
            foreach (array_diff_key($types, array_flip([$index])) as $otherType) {
                $type->assertCanUnionWith($otherType);
            }
        }

        $this->types = $types;
    }

    /** @return non-empty-string */
    public function toString(): string
    {
        return implode(
            '|',
            array_map(
                static fn(AtomicType|IntersectionType $type): string => $type instanceof IntersectionType
                    ? '(' . $type->toString() . ')'
                    : $type->toString(),
                $this->types
            )
        );
    }

    /** @return non-empty-string */
    public function fullyQualifiedName(): string
    {
        return implode(
            '|',
            array_map(
                static fn(AtomicType|IntersectionType $type): string => $type instanceof IntersectionType
                    ? '(' . $type->fullyQualifiedName() . ')'
                    : $type->fullyQualifiedName(),
                $this->types
            )
        );
    }
}
