<?php

namespace Laminas\Code\Generator\EnumGenerator\Cases;

use InvalidArgumentException;
use ReflectionEnum;
use ReflectionEnumBackedCase;
use ReflectionEnumUnitCase;

use function array_key_exists;
use function array_map;
use function assert;

use const PHP_VERSION_ID;

/** @internal */
final class CaseFactory
{
    /**
     * @psalm-param array{
     *      name: non-empty-string,
     *      pureCases: list<non-empty-string>,
     * }|array{
     *      name: non-empty-string,
     *      backedCases: array{
     *          type: 'int',
     *          cases: array<non-empty-string, int>,
     *      }|array{
     *          type: 'string',
     *          cases: array<non-empty-string, non-empty-string>,
     *      },
     * } $options
     * @return BackedCases|PureCases
     */
    public static function fromOptions(array $options)
    {
        if (array_key_exists('pureCases', $options) && ! array_key_exists('backedCases', $options)) {
            return PureCases::fromCases($options['pureCases']);
        }

        assert(! array_key_exists('pureCases', $options) && array_key_exists('backedCases', $options));
        return BackedCases::fromCasesWithType($options['backedCases']['cases'], $options['backedCases']['type']);
    }

    /**
     * @return BackedCases|PureCases
     */
    public static function fromReflectionCases(ReflectionEnum $enum)
    {
        if (PHP_VERSION_ID < 80100) {
            throw new InvalidArgumentException('This feature only works from PHP 8.1 onwards.');
        }

        $backingType = $enum->getBackingType();

        if ($backingType === null) {
            $callback  = static fn(ReflectionEnumUnitCase $singleCase): string => $singleCase->getName();
            $pureCases = array_map($callback, $enum->getCases());

            return PureCases::fromCases($pureCases);
        }

        $backedCases = [];
        foreach ($enum->getCases() as $singleCase) {
            $backedCases[$singleCase->getName()] = $singleCase->getBackingValue();
        }

        return BackedCases::fromCasesWithType($backedCases, $backingType->getName());
    }
}
