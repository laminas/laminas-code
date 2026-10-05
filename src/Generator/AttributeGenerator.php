<?php

namespace Laminas\Code\Generator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use UnitEnum;

use function addcslashes;
use function array_is_list;
use function array_keys;
use function array_map;
use function assert;
use function get_debug_type;
use function implode;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function ltrim;
use function preg_match;
use function sprintf;
use function var_export;

/**
 * Generates a single PHP attribute declaration, such as `#[\Foo\Bar(1, baz: 'taz')]`.
 *
 * Attribute names are always generated as fully qualified names, so that the generated
 * code is valid regardless of the `use` statements (or lack thereof) surrounding it.
 *
 * @psalm-immutable
 */
final class AttributeGenerator implements GeneratorInterface
{
    private const LABEL_PATTERN         = '[a-zA-Z_\\x80-\\xff][a-zA-Z0-9_\\x80-\\xff]*';
    private const NAME_PATTERN          = '/^' . self::LABEL_PATTERN . '(\\\\' . self::LABEL_PATTERN . ')*$/';
    private const ARGUMENT_NAME_PATTERN = '/^' . self::LABEL_PATTERN . '$/';

    /**
     * @param non-empty-string $name      fully qualified attribute name, without leading `\`
     * @param array<array-key, mixed> $arguments positional (`int` keys) and named (`string` keys) arguments
     */
    private function __construct(
        private readonly string $name,
        private readonly array $arguments,
    ) {
    }

    /**
     * @param string $name fully qualified attribute name: a leading `\` is accepted, and stripped
     * @param array<array-key, mixed> $arguments positional (`int` keys) and named (`string` keys) arguments
     * @throws InvalidArgumentException
     */
    public static function fromName(string $name, array $arguments = []): self
    {
        $normalizedName = ltrim($name, '\\');

        if (! preg_match(self::NAME_PATTERN, $normalizedName)) {
            throw new InvalidArgumentException(sprintf(
                'Provided attribute name "%s" is not a valid class name',
                $name
            ));
        }

        assert('' !== $normalizedName);

        self::assertValidArgumentOrder($arguments);

        return new self($normalizedName, $arguments);
    }

    /**
     * @template T of object
     * @param ReflectionAttribute<T> $reflectionAttribute
     * @throws InvalidArgumentException
     */
    public static function fromReflectionAttribute(ReflectionAttribute $reflectionAttribute): self
    {
        return self::fromName($reflectionAttribute->getName(), $reflectionAttribute->getArguments());
    }

    /**
     * @return list<self>
     * @throws InvalidArgumentException
     */
    public static function fromReflector(
        ReflectionClass|ReflectionClassConstant|ReflectionFunction
        |ReflectionMethod|ReflectionParameter|ReflectionProperty $reflector
    ): array {
        $attributes = [];

        foreach ($reflector->getAttributes() as $attribute) {
            $attributes[] = self::fromReflectionAttribute($attribute);
        }

        return $attributes;
    }

    /** @return non-empty-string */
    public function getName(): string
    {
        return $this->name;
    }

    /** @return array<array-key, mixed> */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return non-empty-string
     * @throws InvalidArgumentException
     */
    public function generate(): string
    {
        if ([] === $this->arguments) {
            return '#[\\' . $this->name . ']';
        }

        $arguments = [];

        /** @var mixed $value */
        foreach ($this->arguments as $key => $value) {
            $arguments[] = is_int($key)
                ? self::generateValue($value)
                : $key . ': ' . self::generateValue($value);
        }

        return '#[\\' . $this->name . '(' . implode(', ', $arguments) . ')]';
    }

    /**
     * Named arguments cannot be followed by positional ones: rejecting that upfront
     * prevents the generation of code that cannot be parsed.
     *
     * @param array<array-key, mixed> $arguments
     * @throws InvalidArgumentException
     */
    private static function assertValidArgumentOrder(array $arguments): void
    {
        $namedArgumentSeen = false;

        foreach (array_keys($arguments) as $key) {
            if (is_string($key)) {
                if ('' === $key || ! preg_match(self::ARGUMENT_NAME_PATTERN, $key)) {
                    throw new InvalidArgumentException(sprintf(
                        'Provided argument name "%s" is not a valid parameter name',
                        $key
                    ));
                }

                $namedArgumentSeen = true;

                continue;
            }

            if ($namedArgumentSeen) {
                throw new InvalidArgumentException(
                    'Positional arguments cannot follow named arguments in an attribute declaration'
                );
            }
        }
    }

    /**
     * Attribute arguments are constant expressions: only a strict subset of PHP values
     * can be represented in them.
     *
     * @throws InvalidArgumentException
     * @psalm-pure
     */
    private static function generateValue(mixed $value): string
    {
        if (null === $value) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            // `var_export()` is the only reliable way to keep the value a float in the generated code
            return var_export($value, true);
        }

        if (is_string($value)) {
            return "'" . addcslashes($value, "\\'") . "'";
        }

        if ($value instanceof UnitEnum) {
            /** @psalm-suppress ImpurePropertyFetch enum cases are immutable by design */
            return '\\' . $value::class . '::' . $value->name;
        }

        if (is_array($value)) {
            return '[' . implode(', ', self::generateArrayEntries($value)) . ']';
        }

        throw new InvalidArgumentException(sprintf(
            'Type "%s" cannot be used as an attribute argument',
            get_debug_type($value)
        ));
    }

    /**
     * @param array<array-key, mixed> $value
     * @return list<string>
     * @throws InvalidArgumentException
     * @psalm-pure
     */
    private static function generateArrayEntries(array $value): array
    {
        if (array_is_list($value)) {
            return array_map(self::generateValue(...), $value);
        }

        $entries = [];

        /** @var mixed $item */
        foreach ($value as $key => $item) {
            $entries[] = self::generateValue($key) . ' => ' . self::generateValue($item);
        }

        return $entries;
    }
}
