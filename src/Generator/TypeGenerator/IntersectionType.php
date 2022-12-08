<?php

declare(strict_types=1);

namespace Laminas\Code\Generator\TypeGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;

use function array_diff_key;
use function array_flip;
use function array_map;
use function implode;
use function sprintf;
use function str_contains;
use function usort;

/**
 * @internal the {@see IntersectionType} is an implementation detail of the type generator,
 *
 * @psalm-immutable
 */
final class IntersectionType
{
    /** @var non-empty-list<AtomicType> sorted, at least 2 values always present */
    private readonly array $types;

    /**
     * @param non-empty-list<AtomicType> $types at least 2 values needed
     * @throws InvalidArgumentException If the given types cannot intersect.
     */
    public function __construct(array $types)
    {
        usort(
            $types,
            static fn(AtomicType $a, AtomicType $b): int => $a->type <=> $b->type
        );

        foreach ($types as $index => $atomicType) {
            foreach (array_diff_key($types, array_flip([$index])) as $otherType) {
                $atomicType->assertCanIntersectWith($otherType);
            }
        }

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

    /** @throws InvalidArgumentException When the union is not possible. */
    public function assertCanUnionWith(AtomicType|self $other): void
    {
        if ($other instanceof AtomicType) {
            foreach ($this->types as $type) {
                $type->assertCanUnionWith($other);
            }

            return;
        }

        $thisString  = $this->toString();
        $otherString = $other->toString();

        if (str_contains($thisString, $otherString) || str_contains($otherString, $thisString)) {
            throw new InvalidArgumentException(sprintf(
                'Types "%s" and "%s" cannot be intersected, as they include each other',
                $thisString,
                $otherString
            ));
        }
    }
}
