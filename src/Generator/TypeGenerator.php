<?php

namespace Laminas\Code\Generator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\TypeGenerator\AtomicType;
use ReflectionClass;
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

        assert($type instanceof ReflectionNamedType || $type instanceof ReflectionUnionType);

        return self::fromTypeString(implode(
            '|',
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
        $types                        = array_map([AtomicType::class, 'fromString'], explode('|', $trimmedNullable));

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

            return new self($types, $nullable);
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

            $atomicType->assertCanUnionWith($otherTypes);
        }

        return new self($types, $nullable);
    }

    /**
     * @param AtomicType[]                     $types
     * @psalm-param non-empty-list<AtomicType> $types
     */
    private function __construct(array $types, bool $nullable)
    {
        $this->types    = $types;
        $this->nullable = $nullable;
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
    public function generate()
    {
        $typesAsStrings = array_map(
            fn(AtomicType $type): string => $type->fullyQualifiedName(),
            $this->types
        );

        if ($this->nullable) {
            return '?' . implode('|', $typesAsStrings);
        }

        return implode('|', $typesAsStrings);
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
    public function __toString()
    {
        return implode('|', array_map(
            fn(AtomicType $type): string => $type->type,
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
    private static function trimNullable($type)
    {
        if (0 === strpos($type, '?')) {
            return [true, substr($type, 1)];
        }

        return [false, $type];
    }
}
