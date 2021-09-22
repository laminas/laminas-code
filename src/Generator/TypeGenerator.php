<?php

namespace Laminas\Code\Generator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\TypeGenerator\AtomicType;
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
use function strpos;
use function strtolower;
use function substr;
use function usort;

/** @psalm-immutable */
final class TypeGenerator implements GeneratorInterface
{
    /**
     * @var AtomicType[]
     * @psalm-var non-empty-list<AtomicType>
     */
    private array $types;
    private bool $nullable;
    private bool $isIntersection;

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

        $isIntersection = false;
        $separator      = '|';

        if (false !== strpos($type, '&')) {
            $isIntersection = true;
            $separator      = '&';
        }

        $types = array_map([AtomicType::class, 'fromString'], explode($separator, $trimmedNullable));

        usort(
            $types,
            static fn(AtomicType $left, AtomicType $right): int
                => [$left->sortIndex, $left->type] <=> [$right->sortIndex, $right->type]
        );

        assert([] !== $types);

        if (1 === count($types)) {
            $types[0]->assertCanBeAStandaloneType();

            if ($nullable) {
                $types[0]->assertCanBeStandaloneNullable();
            }

            return new self($types, $nullable, $isIntersection);
        }

        if ($nullable) {
            throw new InvalidArgumentException(sprintf(
                'Type "%s" is a union type, and therefore cannot be also marked nullable with the "?" prefix',
                $type
            ));
        }

        foreach ($types as $index => $atomicType) {
            $otherTypes = array_diff_key($types, array_flip([$index]));

            assert([] !== $otherTypes, 'There are always 2 or more types in a union type');

            if ($isIntersection) {
                $atomicType->assertCanIntersectWith($otherTypes);
            } else {
                $atomicType->assertCanUnionWith($otherTypes);
            }
        }

        return new self($types, $nullable, $isIntersection);
    }

    /**
     * @param AtomicType[]                     $types
     * @psalm-param non-empty-list<AtomicType> $types
     */
    private function __construct(array $types, bool $nullable, bool $isIntersection)
    {
        $this->types          = $types;
        $this->nullable       = $nullable;
        $this->isIntersection = $isIntersection;
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
        $typesAsStrings = array_map(
            static fn (AtomicType $type): string => $type->fullyQualifiedName(),
            $this->types
        );

        if ($this->nullable) {
            return '?' . implode($this->atomicTypesSeparator(), $typesAsStrings);
        }

        return implode($this->atomicTypesSeparator(), $typesAsStrings);
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
        return implode($this->atomicTypesSeparator(), array_map(
            static fn (AtomicType $type): string => $type->type,
            $this->types
        ));
    }

    /**
     * @param string $type
     * @return bool[]|string[] ordered tuple, first key represents whether the type is nullable, second is the
     *                         trimmed string
     * @psalm-return array{bool, string}
     * @psalm-pure
     */
    private static function trimNullable($type): array
    {
        if (0 === strpos($type, '?')) {
            return [true, substr($type, 1)];
        }

        return [false, $type];
    }

    /** @psalm-return '|'|'&' */
    private function atomicTypesSeparator(): string
    {
        return $this->isIntersection
            ? '&'
            : '|';
    }
}
