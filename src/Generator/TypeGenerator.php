<?php

namespace Laminas\Code\Generator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\TypeGenerator\AtomicType;
use Laminas\Code\Generator\TypeGenerator\CompositeType;
use Laminas\Code\Generator\TypeGenerator\TypeInterface;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

use function array_diff_key;
use function array_flip;
use function array_map;
use function assert;
use function count;
use function explode;
use function implode;
use function sprintf;
use function str_contains;
use function str_starts_with;
use function strpos;
use function strtolower;
use function substr;
use function usort;

/** @psalm-immutable */
final class TypeGenerator implements GeneratorInterface
{
    private const NULL_MARKER = '?';

    /**
     * @internal
     *
     * @psalm-pure
     */
    public static function fromReflectionType(
        ?ReflectionType $type,
        ?ReflectionClass $currentClass
    ): ?self {
        if (null === $type) {
            return null;
        }

        // Having to go through `fromTypeString` leads to interesting invalid types as "acceptable", but that's neither
        // a security issue, nor a major problem, since {@see ReflectionType} should itself produce valid/usable strings
        return self::fromTypeString(self::reflectionTypeToString($type, $currentClass));
    }

    /** @psalm-pure */
    private static function reflectionTypeToString(ReflectionType $type, ?ReflectionClass $currentClass): string
    {
        assert(
            $type instanceof ReflectionNamedType
            || $type instanceof ReflectionUnionType
            || $type instanceof ReflectionIntersectionType
        );

        if ($type instanceof ReflectionNamedType) {
            return self::reflectionNamedTypeToString($type, $currentClass);
        }

        return implode(
            $type instanceof ReflectionIntersectionType
                ? CompositeType::INTERSECTION_SEPARATOR
                : CompositeType::UNION_SEPARATOR,
            array_map(
                static function (ReflectionType $type) use ($currentClass): string {
                    if ($type instanceof ReflectionIntersectionType) {
                        return '(' . self::reflectionTypeToString($type, $currentClass) . ')';
                    }

                    return self::reflectionTypeToString($type, $currentClass);
                },
                $type->getTypes()
            )
        );
    }

    /** @psalm-pure */
    private static function reflectionNamedTypeToString(
        ReflectionNamedType $type,
        ?ReflectionClass $currentClass
    ): string {
        $lowerCaseName = strtolower($type->getName());

        if ('mixed' === $lowerCaseName || 'null' === $lowerCaseName) {
            // `mixed` and `null` are implicitly nullable, therefore we need to skip adding nullability markers to it
            return $lowerCaseName;
        }

        $nullabilityMarker = $type->allowsNull()
            ? self::NULL_MARKER
            : '';

        if ('self' === $lowerCaseName && $currentClass) {
            return $nullabilityMarker . $currentClass->getName();
        }

        if ('parent' === $lowerCaseName && $currentClass && $parentClass = $currentClass->getParentClass()) {
            return $nullabilityMarker . $parentClass->getName();
        }

        return $nullabilityMarker . $type->getName();
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
            return new self(AtomicType::fromString($trimmedNullable), $nullable);
        }

        if ($nullable) {
            throw new InvalidArgumentException(sprintf(
                'Type "%s" is a union type, and therefore cannot be also marked nullable with the "?" prefix',
                $type
            ));
        }

        return new self(CompositeType::fromString($trimmedNullable), $nullable);
    }

    private function __construct(private readonly TypeInterface $type, private readonly bool $nullable)
    {
        if ($nullable && $this->type instanceof AtomicType) {
            $this->type->assertCanBeStandaloneNullable();
        }
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
        return ($this->nullable ? self::NULL_MARKER : '') . $this->type->toString();
    }

    public function equals(TypeGenerator $otherType): bool
    {
        return $this->generate() === $otherType->generate();
    }

    /**
     * @return string the cleaned type string. Please note that this value is not suitable for code generation,
     *                since the returned value does not include any root namespace prefixes, when applicable,
     *                and therefore the values cannot be used as FQCN in generated code.
     */
    public function __toString(): string
    {
        return $this->type->__toString();
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
