<?php

declare(strict_types=1);

namespace Laminas\Code\Generator\TypeGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;

use function array_map;
use function explode;
use function preg_match;
use function sprintf;
use function str_contains;
use function trim;

/**
 * @internal the {@see CompositeType} is an implementation detail of the type generator,
 *
 * @psalm-immutable
 * @final
 */
abstract class CompositeType
{
    public const UNION_SEPARATOR        = '|';
    public const INTERSECTION_SEPARATOR = '&';

    /** @psalm-pure */
    public static function fromString(string $type): UnionType|IntersectionType|AtomicType
    {
        if (str_contains($type, self::UNION_SEPARATOR)) {
            // This horrible regular expression verifies that union delimiters `|` are never contained
            // in parentheses, and that all intersection `&` are contained in parentheses. It's simplistic,
            // and it will crash with very large broken types, but that's sufficient for our **current**
            // use-case.
            // If this becomes more problematic, an actual parser is a better (although slower) alternative.
            if (1 !== preg_match('/^(([|]|[^()&]+)+|(\(([&]|[^|()]+)\))+)+$/', $type)) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid type syntax "%s": intersections in a union must be surrounded by "(" and ")"',
                    $type
                ));
            }

            /** @var non-empty-list<IntersectionType|AtomicType> $typesInUnion */
            $typesInUnion = array_map(
                self::fromString(...),
                array_map(
                    static fn (string $type): string => trim($type, '()'),
                    explode(self::UNION_SEPARATOR, $type)
                )
            );

            return new UnionType($typesInUnion);
        }

        if (str_contains($type, self::INTERSECTION_SEPARATOR)) {
            /** @var non-empty-list<AtomicType> $typesInIntersection */
            $typesInIntersection = array_map(self::fromString(...), explode(self::INTERSECTION_SEPARATOR, $type));

            return new IntersectionType($typesInIntersection);
        }

        return AtomicType::fromString($type);
    }
}
