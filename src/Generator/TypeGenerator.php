<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Generator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;

final class TypeGenerator implements GeneratorInterface
{
    /**
     * @var bool
     */
    private $isInternalPhpType;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string[]
     *
     * @link http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration
     */
    private static $internalPhpTypes = ['int', 'float', 'string', 'bool', 'array', 'callable'];

    // @codingStandardsIgnoreStart
    /**
     * @var string a regex pattern to match valid class names or types
     */
    private static $validIdentifierMatcher = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\\\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*$/';
    // @codingStandardsIgnoreEnd

    /**
     * @param string $type
     *
     * @return TypeGenerator
     *
     * @throws InvalidArgumentException
     */
    public static function fromTypeString($type)
    {
        list($wasTrimmed, $trimmedType) = self::trimType($type);

        if (! preg_match(self::$validIdentifierMatcher, $trimmedType)) {
            throw new InvalidArgumentException(sprintf(
                'Provided type "%s" is invalid: must conform "%s"',
                $type,
                self::$validIdentifierMatcher
            ));
        }

        $isInternalPhpType = self::isInternalPhpType($trimmedType);

        if ($wasTrimmed && $isInternalPhpType) {
            throw new InvalidArgumentException(sprintf(
                'Provided type "%s" is an internal PHP type, but was provided with a namespace separator prefix',
                $type
            ));
        }

        $instance = new self();

        $instance->type              = $trimmedType;
        $instance->isInternalPhpType = self::isInternalPhpType($trimmedType);

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
        if ($this->isInternalPhpType) {
            return strtolower($this->type);
        }

        return '\\' . $this->type;
    }

    /**
     * @return string the cleaned type string
     */
    public function __toString()
    {
        return ltrim($this->generate(), '\\');
    }

    /**
     * @param string $type
     *
     * @return bool[]|int[] ordered tuple, first key represents whether the values was trimmed, second is the
     *                      trimmed string
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
