<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Generator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;

use function in_array;
use function ltrim;
use function preg_match;
use function sprintf;
use function strpos;
use function strtolower;
use function substr;

final class TypeGenerator implements GeneratorInterface
{
    /**
     * @var array
     */
    private $type;

    /**
     * @var string[]
     *
     * @link http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration
     */
    private static $internalPhpTypes = [
        'void',
        'int',
        'float',
        'string',
        'bool',
        'array',
        'callable',
        'iterable',
        'object',
        'null'
    ];

    /**
     * @var string a regex pattern to match valid class names or types
     */
    private static $validIdentifierMatcher = '/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*'
        . '(\\\\[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)*$/';

    /**
     * @param string $type
     *
     * @return TypeGenerator
     *
     * @throws InvalidArgumentException
     */
    public static function fromTypeString($type)
    {
        $typeList = [];

        foreach (explode('|', $type) as $typeString) {
            list($nullable, $trimmedNullable) = self::trimNullable($typeString);
            list($wasTrimmed, $trimmedType) = self::trimType($trimmedNullable);

            if (! preg_match(self::$validIdentifierMatcher, $trimmedType)) {
                throw new InvalidArgumentException(sprintf(
                    'Provided type "%s" is invalid: must conform "%s"',
                    $typeString,
                    self::$validIdentifierMatcher
                ));
            }

            $isInternalPhpType = self::isInternalPhpType($trimmedType);

            if ($wasTrimmed && $isInternalPhpType) {
                throw new InvalidArgumentException(sprintf(
                    'Provided type "%s" is an internal PHP type, but was provided with a namespace separator prefix',
                    $typeString
                ));
            }

            if ($nullable && $isInternalPhpType && 'void' === strtolower($trimmedType)) {
                throw new InvalidArgumentException(sprintf('Provided type "%s" cannot be nullable', $typeString));
            }

            $typeList[] = [
                'isInternalPhpType' => $isInternalPhpType,
                'type' => $trimmedType,
                'nullable' => $nullable,
            ];
        }

        $instance = new self();

        $instance->type = $typeList;

        return $instance;
    }

    private function __construct()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function generate()
    {
        $str = [];

        foreach ($this->type as $type) {
            $nullable = $type['nullable'] ? '?' : '';

            if ($type['isInternalPhpType']) {
                $str[] = $nullable.strtolower($type['type']);
                continue;
            }

            $str[] = $nullable.'\\' . $type['type'];
        }

        return implode('|', $str);
    }

    /**
     * @return string the cleaned type string
     */
    public function __toString()
    {
        return ltrim($this->generate(), '?\\');
    }

    /**
     * @param string $type
     *
     * @return bool[]|string[] ordered tuple, first key represents whether the type is nullable, second is the
     *                         trimmed string
     */
    private static function trimNullable($type)
    {
        if (0 === strpos($type, '?')) {
            return [true, substr($type, 1)];
        }

        return [false, $type];
    }

    /**
     * @param string $type
     *
     * @return bool[]|string[] ordered tuple, first key represents whether the values was trimmed, second is the
     *                         trimmed string
     */
    private static function trimType($type)
    {
        if (0 === strpos($type, '\\')) {
            return [true, substr($type, 1)];
        }

        return [false, $type];
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    private static function isInternalPhpType($type)
    {
        return in_array(strtolower($type), self::$internalPhpTypes, true);
    }
}
