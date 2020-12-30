<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Generator\TypeGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;

use function array_filter;
use function array_key_exists;
use function assert;
use function implode;
use function preg_match;
use function sprintf;
use function strtolower;
use function substr;

/**
 * Represents a single/indivisible (atomic) type, as supported by PHP.
 * This means that this object can be composed into more complex union, intersection
 * and nullable types.
 *
 * @internal the {@see AtomicType} is an implementation detail of the type generator,
 *
 * @psalm-immutable
 */
final class AtomicType
{
    /**
     * Built-in type sorting, ascending.
     *
     * @psalm-var array<non-empty-string, positive-int>
     */
    private const BUILT_IN_TYPES_PRECEDENCE = [
        'bool'     => 1,
        'int'      => 2,
        'float'    => 3,
        'string'   => 4,
        'array'    => 5,
        'callable' => 6,
        'iterable' => 7,
        'object'   => 8,
        'static'   => 9,
        'mixed'    => 10,
        'void'     => 11,
        'false'    => 12,
        'null'     => 13,
    ];

    /** @psalm-var array<non-empty-string, null> */
    private const NOT_NULLABLE_TYPES = [
        'null'  => null,
        'false' => null,
        'void'  => null,
        'mixed' => null,
    ];

    /** A regex pattern to match valid class/interface/trait names */
    private const VALID_IDENTIFIER_MATCHER = '/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*'
        . '(\\\\[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)*$/';

    /** @psalm-var value-of<AtomicType::BUILT_IN_TYPES_PRECEDENCE>|0 */
    public int $sortIndex;

    /** @psalm-var non-empty-string */
    public string $type;

    /**
     * @psalm-param non-empty-string $type
     * @psalm-param value-of<AtomicType::BUILT_IN_TYPES_PRECEDENCE>|0 $sortIndex
     */
    private function __construct(string $type, int $sortIndex)
    {
        $this->type      = $type;
        $this->sortIndex = $sortIndex;
    }

    /**
     * @psalm-pure
     * @throws InvalidArgumentException
     */
    public static function fromString(string $type): self
    {
        $trimmedType   = '\\' === ($type[0] ?? '')
            ? substr($type, 1)
            : $type;
        $lowerCaseType = strtolower($trimmedType);

        if (array_key_exists($lowerCaseType, self::BUILT_IN_TYPES_PRECEDENCE)) {
            if ($lowerCaseType !== strtolower($type)) {
                throw new InvalidArgumentException(sprintf(
                    'Provided type "%s" is a built-in type, and should not be prefixed with "\\"',
                    $type
                ));
            }

            return new self($lowerCaseType, self::BUILT_IN_TYPES_PRECEDENCE[$lowerCaseType]);
        }

        if (1 !== preg_match(self::VALID_IDENTIFIER_MATCHER, $trimmedType)) {
            throw new InvalidArgumentException(sprintf(
                'Provided type "%s" is not recognized as a valid expression: '
                . 'it must match "%s" or be one of the built-in types (%s)',
                $type,
                self::VALID_IDENTIFIER_MATCHER,
                implode(', ', self::BUILT_IN_TYPES_PRECEDENCE)
            ));
        }

        assert('' !== $trimmedType);

        return new self($trimmedType, 0);
    }

    /** @psalm-pure */
    public static function null(): self
    {
        return new self('null', self::BUILT_IN_TYPES_PRECEDENCE['null']);
    }

    /** @psalm-return non-empty-string */
    public function fullyQualifiedName(): string
    {
        return array_key_exists($this->type, self::BUILT_IN_TYPES_PRECEDENCE)
            ? $this->type
            : '\\' . $this->type;
    }

    /**
     * @psalm-param non-empty-array<self> $others
     * @throws InvalidArgumentException
     */
    public function assertCanUnionWith(array $others): void
    {
        if (
            'mixed' === $this->type
            || 'void' === $this->type
        ) {
            throw new InvalidArgumentException(sprintf(
                'Type "%s" cannot be composed in a union with any other types',
                $this->type
            ));
        }

        foreach ($others as $other) {
            if ($other->type === $this->type) {
                throw new InvalidArgumentException(sprintf(
                    'Type "%s" cannot be composed in a union with the same type "%s"',
                    $this->type,
                    $other->type
                ));
            }
        }

        if (
            $this->requiresUnionWithStandaloneType() &&
            [] === array_filter($others, static fn (self $type): bool => ! $type->requiresUnionWithStandaloneType())
        ) {
            throw new InvalidArgumentException(sprintf(
                'Type "%s" requires to be composed with non-standalone types',
                $this->type
            ));
        }
    }

    /** @throws InvalidArgumentException */
    public function assertCanBeAStandaloneType(): void
    {
        if ($this->requiresUnionWithStandaloneType()) {
            throw new InvalidArgumentException(sprintf(
                'Type "%s" cannot be used standalone, and must be part of a union type',
                $this->type
            ));
        }
    }

    /** @throws InvalidArgumentException */
    public function assertCanBeStandaloneNullable(): void
    {
        if (array_key_exists($this->type, self::NOT_NULLABLE_TYPES)) {
            throw new InvalidArgumentException(sprintf(
                'Type "%s" cannot be nullable',
                $this->type
            ));
        }
    }

    private function requiresUnionWithStandaloneType(): bool
    {
        return 'null' === $this->type || 'false' === $this->type;
    }
}
