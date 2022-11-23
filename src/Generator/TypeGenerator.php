<?php

namespace Laminas\Code\Generator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\TypeGenerator\AtomicType;
use Laminas\Code\Generator\TypeGenerator\CompositeType;
use Laminas\Code\Generator\TypeGenerator\Type;
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

        assert(
            $type instanceof ReflectionNamedType
            || $type instanceof ReflectionUnionType
            || $type instanceof ReflectionIntersectionType
        );

        // Having to go through `fromTypeString` leads to interesting invalid types as "acceptable", but that's neither
        // a security issue, nor a major problem, since {@see ReflectionType} should itself produce valid/usable strings
        return self::fromTypeString(implode(
            $type instanceof ReflectionIntersectionType
                ? '&'
                : '|',
            array_map(
                static fn(ReflectionNamedType $type): string => self::reflectionNamedTypeToString($type, $currentClass),
                $type instanceof ReflectionNamedType
                    ? [$type]
                    : $type->getTypes()
            )
        ));
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
            ? '?'
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

        if (!str_contains($trimmedNullable, CompositeType::INTERSECTION_SEPARATOR)
            && !str_contains($trimmedNullable, CompositeType::UNION_SEPARATOR)) {
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

    /**
     * @param Type $type
     */
    private function __construct(private readonly Type $type, private readonly bool $nullable)
    {
        if ($nullable) {
            if ($this->type instanceof AtomicType) {
                $this->type->assertCanBeStandaloneNullable();
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Type "%s" is a composite type, and therefore cannot be also marked nullable with the "?" prefix',
                    $this->type->toString()
                ));
            }
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
        return ($this->nullable ? '?' : '') . $this->type->toString();
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
     * @param string $type
     * @return bool[]|string[] ordered tuple, first key represents whether the type is nullable, second is the
     *                         trimmed string
     * @psalm-return array{bool, string}
     * @psalm-pure
     */
    private static function trimNullable(string $type): array
    {
        if (str_starts_with($type, '?')) {
            return [true, substr($type, 1)];
        }

        return [false, $type];
    }
}
