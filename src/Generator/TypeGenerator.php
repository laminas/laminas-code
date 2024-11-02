<?php

declare(strict_types=1);

namespace Laminas\Code\Generator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\TypeGenerator\AtomicType;
use Laminas\Code\Generator\TypeGenerator\CompositeType;
use Laminas\Code\Generator\TypeGenerator\IntersectionType;
use Laminas\Code\Generator\TypeGenerator\UnionType;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;
use Stringable;

use function array_map;
use function sprintf;
use function str_contains;
use function str_starts_with;
use function substr;

/** @psalm-immutable */
final class TypeGenerator implements GeneratorInterface, Stringable
{
    private const NULL_MARKER = '?';

    private function __construct(
        private readonly UnionType|IntersectionType|AtomicType $type,
        private readonly bool $nullable = false
    ) {
        if ($nullable && $type instanceof AtomicType) {
            $type->assertCanBeStandaloneNullable();
        }
    }

    /**
     * @internal
     *
     * @psalm-pure
     */
    public static function fromReflectionType(
        ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $type,
        ?ReflectionClass $currentClass
    ): ?self {
        if (null === $type) {
            return null;
        }

        if ($type instanceof ReflectionUnionType) {
            return new self(
                new UnionType(array_map(
                    static fn(
                        ReflectionIntersectionType|ReflectionNamedType $type
                    ): IntersectionType|AtomicType => $type instanceof ReflectionNamedType
                        ? AtomicType::fromReflectionNamedTypeAndClass($type, $currentClass)
                        : self::fromIntersectionType($type, $currentClass),
                    $type->getTypes()
                )),
                false
            );
        }

        if ($type instanceof ReflectionIntersectionType) {
            return new self(self::fromIntersectionType($type, $currentClass), false);
        }

        $atomicType = AtomicType::fromReflectionNamedTypeAndClass($type, $currentClass);

        return new self(
            $atomicType,
            $atomicType->type !== 'mixed' && $atomicType->type !== 'null' && $type->allowsNull()
        );
    }

    /** @psalm-pure */
    private static function fromIntersectionType(
        ReflectionIntersectionType $intersectionType,
        ?ReflectionClass $currentClass
    ): IntersectionType {
        return new IntersectionType(array_map(
            static fn(
                ReflectionNamedType $type
            ): AtomicType => AtomicType::fromReflectionNamedTypeAndClass($type, $currentClass),
            $intersectionType->getTypes()
        ));
    }

    /**
     * @throws InvalidArgumentException
     * @psalm-pure
     */
    public static function fromTypeString(string $type): self
    {
        [$nullable, $trimmedNullable] = self::trimNullable($type);

        if (
            ! str_contains($trimmedNullable, CompositeType::INTERSECTION_SEPARATOR)
            && ! str_contains($trimmedNullable, CompositeType::UNION_SEPARATOR)
        ) {
            return new self(CompositeType::fromString($trimmedNullable), $nullable);
        }

        if ($nullable) {
            throw new InvalidArgumentException(sprintf(
                'Type "%s" is a union type, and therefore cannot be also marked nullable with the "?" prefix',
                $type
            ));
        }

        return new self(CompositeType::fromString($trimmedNullable));
    }

    /**
     * {@inheritDoc}
     *
     * Generates the type string, including FQCN "\\" prefix, so that
     * it can directly be used within any code snippet, regardless of
     * imports.
     *
     * @psalm-return non-empty-string
     */
    public function generate(): string
    {
        return ($this->nullable ? self::NULL_MARKER : '') . $this->type->fullyQualifiedName();
    }

    public function equals(TypeGenerator $otherType): bool
    {
        return $this->generate() === $otherType->generate();
    }

    /**
     * @return non-empty-string the cleaned type string. Note that this value is not suitable for code generation,
     *                          since the returned value does not include any root namespace prefixes, when applicable,
     *                          and therefore the values cannot be used as FQCN in generated code.
     */
    public function __toString(): string
    {
        return $this->type->toString();
    }

    /**
     * @return bool[]|string[] ordered tuple, first key represents whether the type is nullable, second is the
     *                         trimmed string
     * @psalm-return array{bool, string}
     * @psalm-pure
     */
    private static function trimNullable(string $type): array
    {
        if (str_starts_with($type, self::NULL_MARKER)) {
            return [true, substr($type, 1)];
        }

        return [false, $type];
    }
}
