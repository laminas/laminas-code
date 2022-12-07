<?php

declare(strict_types=1);

namespace Laminas\Code\Generator\TypeGenerator;

use function array_map;
use function implode;
use function usort;

/**
 * @internal the {@see IntersectionType} is an implementation detail of the type generator,
 *
 * @psalm-immutable
 */
final class IntersectionType
{
    /** @var non-empty-list<AtomicType> at least 2 values always present */
    private readonly array $types;
    
    /** @param non-empty-list<AtomicType> $types at least 2 values needed */
    public function __construct(array $types)
    {
        usort(
            $types,
            static fn (AtomicType $a, AtomicType $b): int => [$a->sortIndex, $a->type] <=> [$b->sortIndex, $b->type]
        );

        $this->types = $types;
    }

    /** @return non-empty-string */
    public function toString(): string
    {
        return implode(
            '&',
            array_map(static fn(AtomicType $type): string => $type->toString(), $this->types)
        );
    }

    /** @return non-empty-string */
    public function fullyQualifiedName(): string
    {
        return implode(
            '&',
            array_map(static fn(AtomicType $type): string => $type->fullyQualifiedName(), $this->types)
        );
    }
}
