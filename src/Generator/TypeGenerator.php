<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator;

use Zend\Code\Generator\Exception\InvalidArgumentException;

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
     */
    private static $internalPhpTypes = ['int', 'float', 'string', 'bool', 'array', 'callable'];

    /**
     * @var string a regex pattern to match valid class names or types
     */
    private static $validIdentifierMatcher = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\\\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*$/';

    /**
     * @param string $type
     *
     * @return TypeGenerator
     *
     * @throws InvalidArgumentException
     */
    public static function fromTypeString(string $type)
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
     * @return string
     */
    public function __toString()
    {
        return $this->generate();
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
